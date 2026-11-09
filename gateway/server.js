import express from 'express';
import makeWASocket, { DisconnectReason, useMultiFileAuthState } from '@whiskeysockets/baileys';
import pino from 'pino';
import qrcode from 'qrcode-terminal';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;

app.use(express.json());

let sock = null;
let isConnected = false;

async function connectToWhatsApp() {
    const sessionDir = path.join(__dirname, 'session');
    const { state, saveCreds } = await useMultiFileAuthState(sessionDir);

    sock = makeWASocket({
        auth: state,
        logger: pino({ level: 'silent' }),
        printQRInTerminal: false,
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', (update) => {
        const { connection, lastDisconnect, qr } = update;

        if (qr) {
            console.log('[WA Gateway] Scan this QR code with WhatsApp (Linked Devices):');
            qrcode.generate(qr, { small: true });
        }

        if (connection === 'close') {
            isConnected = false;
            const statusCode = lastDisconnect?.error?.output?.statusCode;
            const shouldReconnect = statusCode !== DisconnectReason.loggedOut;
            console.log(`[WA Gateway] Connection closed (code: ${statusCode}). Reconnecting: ${shouldReconnect}`);

            if (shouldReconnect) {
                connectToWhatsApp();
            } else {
                console.log('[WA Gateway] Device logged out. Please delete gateway/session and re-scan QR code.');
            }
        } else if (connection === 'open') {
            isConnected = true;
            console.log('[WA Gateway] Connection ready and authenticated');
        }
    });
}

app.post('/api/send-message', async (req, res) => {
    try {
        const { target, message } = req.body;

        if (!target || !message) {
            return res.status(400).json({
                status: false,
                message: 'Target and message are required',
            });
        }

        if (!sock || !isConnected) {
            return res.status(503).json({
                status: false,
                message: 'WhatsApp socket is not connected or ready',
            });
        }

        // Sanitize and normalize target phone number
        let cleaned = String(target).replace(/\D/g, '');
        if (cleaned.startsWith('0')) {
            cleaned = '62' + cleaned.slice(1);
        }

        const jid = `${cleaned}@s.whatsapp.net`;

        await sock.sendMessage(jid, { text: message });

        return res.status(200).json({
            status: true,
            message: 'Message successfully dispatched',
        });
    } catch (error) {
        console.error('[WA Gateway] Error sending message:', error);
        return res.status(500).json({
            status: false,
            message: 'Failed to dispatch message',
            error: error.message,
        });
    }
});

app.listen(PORT, () => {
    console.log(`[WA Gateway] Microservice running on port ${PORT}`);
    connectToWhatsApp();
});

