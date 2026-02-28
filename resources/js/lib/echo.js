let echoInstance = null;

function resolvePort(scheme, configuredPort) {
    if (configuredPort) {
        return Number(configuredPort);
    }

    return scheme === 'https' ? 443 : 80;
}

export async function ensureEcho() {
    if (typeof window === 'undefined') {
        return null;
    }

    if (echoInstance) {
        return echoInstance;
    }

    const [{ default: Echo }, { default: Pusher }] = await Promise.all([
        import('laravel-echo'),
        import('pusher-js'),
    ]);

    window.Pusher = Pusher;

    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
    const host = import.meta.env.VITE_REVERB_HOST ?? window.location.hostname;
    const port = resolvePort(scheme, import.meta.env.VITE_REVERB_PORT);

    echoInstance = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: host,
        wsPort: port,
        wssPort: port,
        forceTLS: scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    window.Echo = echoInstance;

    return echoInstance;
}

export function getEcho() {
    return echoInstance;
}
