import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

let echo: Echo<'reverb'> | null = null

function getEcho() {
  if (echo) return echo

  const key = import.meta.env.VITE_REVERB_APP_KEY
  if (!key) {
    throw new Error('VITE_REVERB_APP_KEY must be configured to receive import notifications.')
  }

  const secure = window.location.protocol === 'https:'
  const socketPort = window.location.port ? Number(window.location.port) : secure ? 443 : 80
  echo = new Echo({
    broadcaster: 'reverb',
    key,
    Pusher,
    wsHost: window.location.hostname,
    wsPort: socketPort,
    wssPort: socketPort,
    forceTLS: secure,
    enabledTransports: ['ws', 'wss'],
    authorizer: (channel) => ({
      authorize: (socketId, callback) => {
        void axios
          .post(
            new URL('/broadcasting/auth', window.location.origin).toString(),
            { socket_id: socketId, channel_name: channel.name },
            {
              headers: { Accept: 'application/json' },
              withCredentials: true,
              withXSRFToken: true,
            },
          )
          .then(({ data }) => callback(null, data))
          .catch((error: unknown) => {
            callback(
              error instanceof Error ? error : new Error('Private channel authorization failed.'),
              null,
            )
          })
      },
    }),
  })

  return echo
}

export function subscribeToImportEvents(userId: number, onFinished: (payload: unknown) => void) {
  const channel = getEcho().private(`user.${userId}`)
  channel
    .listen('.import.finished', onFinished)
    .error((error: unknown) => console.error('Import notification channel failed.', error))

  return () => {
    echo?.leave(`user.${userId}`)
  }
}

export function disconnectRealtime() {
  echo?.disconnect()
  echo = null
}
