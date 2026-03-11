import { mount } from 'svelte'
import './app.css'
import App from './App.svelte'
import { registerSW } from 'virtual:pwa-register'

const app = mount(App, {
  target: document.getElementById('app')!,
})

registerSW({
  immediate: true,
  onRegisteredSW(swUrl, registration) {
    console.log('PWA: Service Worker registrado en', swUrl)
    if (registration) {
      if (registration.active) {
        console.log('PWA: Service Worker ACTIVO — offline disponible')
      }
      registration.addEventListener('updatefound', () => {
        const newWorker = registration.installing
        if (newWorker) {
          newWorker.addEventListener('statechange', () => {
            if (newWorker.state === 'activated') {
              console.log('PWA: Service Worker ACTIVADO — offline disponible')
            }
          })
        }
      })
      setInterval(() => {
        registration.update()
      }, 60 * 60 * 1000)
    }
  },
  onOfflineReady() {
    console.log('PWA: Todos los assets cacheados — app lista para offline')
  },
})

export default app
