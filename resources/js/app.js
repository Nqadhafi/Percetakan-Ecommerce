import './bootstrap'
import '../css/app.css'

import { createApp, h } from 'vue'
import { createInertiaApp, Link, Head } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import { ZiggyVue } from '../../vendor/tightenco/ziggy'
import AppLayout from './Layouts/AppLayout.vue' // ⬅️ layout kita

const appName = import.meta.env.VITE_APP_NAME || 'Laravel'

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  // Inject default layout: kalau page belum set "page.default.layout", pakai AppLayout
  resolve: (name) =>
    resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue'))
      .then((module) => {
        module.default.layout = module.default.layout ?? AppLayout
        return module
      }),
  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) })
    vueApp.use(plugin).use(ZiggyVue)
    // Komponen global Inertia
    vueApp.component('Link', Link)
    vueApp.component('Head', Head)
    vueApp.mount(el)
    return vueApp
  },
  progress: {
    color: '#4B5563',
  },
})
