import type { AxiosInstance } from "axios";
import type { Emitter } from "mitt";
import { Pinia } from "pinia";
import Swal from "sweetalert2";

interface ImportMeta {
  readonly client: any; // Replace 'any' with the appropriate type for 'client'
}

interface PluginInjections {
  $pinia: Pinia
  $event: Emitter
  $listen: (event: string, callback: (data: any) => void) => void
  $axios: AxiosInstance
  $t: (key: string) => string
  $swal: typeof Swal
}

declare module '#app' {
  interface NuxtApp extends PluginInjections {}
}

declare module 'nuxt/dist/app/nuxt' {
  interface NuxtApp extends PluginInjections {}
}

declare module '@vue/runtime-core' {
  interface ComponentCustomProperties extends PluginInjections {}
}

interface MessageBag {
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
  title?: string
}

export {}