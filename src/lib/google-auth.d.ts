declare namespace google.accounts.id {
  interface IdConfiguration {
    client_id: string
    callback: (response: CredentialResponse) => void
    auto_select?: boolean
    cancel_on_tap_outside?: boolean
    context?: 'signin' | 'signup' | 'use'
    itp_support?: boolean
    login_uri?: string
    native_callback?: (response: NativeCredentialResponse) => void
    nonce?: string
    prompt_parent_id?: string
    state_cookie_domain?: string
    ux_mode?: 'popup' | 'redirect'
    allowed_parent_origin?: string | string[]
    intermediate_iframe_close_callback?: () => void
  }

  interface CredentialResponse {
    credential: string
    select_by: string
    clientId?: string
  }

  interface NativeCredentialResponse {
    id: string
    password: string
  }

  interface GsiButtonConfiguration {
    type?: 'standard' | 'icon'
    theme?: 'outline' | 'filled_blue' | 'filled_black'
    size?: 'large' | 'medium' | 'small'
    text?: 'signin_with' | 'signup_with' | 'continue_with' | 'signin'
    shape?: 'rectangular' | 'pill' | 'circle' | 'square'
    logo_alignment?: 'left' | 'center'
    width?: string | number
    locale?: string
  }

  interface PromptNotification {
    isDisplayMoment: () => boolean
    isDisplayed: () => boolean
    isNotDisplayed: () => boolean
    getNotDisplayedReason: () => string
    isSkippedMoment: () => boolean
    getSkippedReason: () => string
    isDismissedMoment: () => boolean
    getDismissedReason: () => string
  }

  function initialize(config: IdConfiguration): void
  function prompt(callback?: (notification: PromptNotification) => void): void
  function renderButton(
    parent: HTMLElement,
    options: GsiButtonConfiguration,
  ): void
  function disableAutoSelect(): void
  function storeCredential(
    credential: { id: string; password: string },
    callback?: () => void,
  ): void
  function cancel(): void
  function revoke(
    hint: string,
    callback: (response: { successful: boolean; error?: string }) => void,
  ): void
}

declare namespace google.accounts.oauth2 {
  interface TokenClientConfig {
    client_id: string
    scope: string
    callback: (response: TokenResponse) => void
    error_callback?: (error: { type: string; message: string }) => void
    prompt?: string
    hint?: string
  }

  interface TokenResponse {
    access_token: string
    expires_in: number
    token_type: string
    scope: string
    error?: string
    error_description?: string
    error_uri?: string
  }

  interface TokenClient {
    requestAccessToken: (config?: { prompt?: string; hint?: string }) => void
  }

  function initTokenClient(config: TokenClientConfig): TokenClient
  function hasGrantedAllScopes(
    tokenResponse: TokenResponse,
    ...scopes: string[]
  ): boolean
  function hasGrantedAnyScope(
    tokenResponse: TokenResponse,
    ...scopes: string[]
  ): boolean
  function revoke(accessToken: string, callback?: () => void): void
}
