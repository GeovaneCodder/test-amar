import axios, { type AxiosError } from 'axios'

export const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: { Accept: 'application/json' },
  withCredentials: true,
  withXSRFToken: true,
})

api.interceptors.response.use(
  (response) => response,
  (error: AxiosError<{ message?: string }>) => {
    const isSessionBootstrapRequest = /\/session\/(login|register|user)$/.test(error.config?.url || '')

    if (error.response?.status === 401 && !isSessionBootstrapRequest) {
      window.dispatchEvent(new Event('auth:unauthorized'))
    }

    return Promise.reject(error)
  },
)

const messageTranslations: Record<string, string> = {
  'The provided credentials are invalid.': 'E-mail ou senha incorretos.',
  'This import cannot be paused.': 'Esta importação não pode ser pausada.',
  'This import cannot be resumed.': 'Esta importação não pode ser retomada.',
  'This import cannot be cancelled.': 'Esta importação não pode ser cancelada.',
  'The CSV file is empty.': 'O arquivo CSV está vazio.',
  'The header must contain date, description, amount, and type.':
    'O cabeçalho deve conter date, description, amount e type.',
  'The row has an invalid number of columns.': 'A linha possui uma quantidade inválida de colunas.',
  'The date must use the YYYY-MM-DD format.': 'A data deve estar no formato AAAA-MM-DD.',
  'The description is required and must not exceed 255 characters.':
    'A descrição é obrigatória e deve ter no máximo 255 caracteres.',
  'The amount must be greater than zero.': 'O valor deve ser maior que zero.',
  'The monetary amount is invalid.': 'O valor informado é inválido.',
  'The given data was invalid.': 'Confira os dados informados.',
  'Unauthenticated.': 'Sua sessão expirou. Entre novamente.',
  Unauthenticated: 'Sua sessão expirou. Entre novamente.',
}

export function translateApiMessage(message: string): string {
  if (messageTranslations[message]) return messageTranslations[message]
  const requiredField = message.match(/^The (.+) field is required\.$/)
  if (requiredField) {
    const fieldNames: Record<string, string> = {
      name: 'nome',
      email: 'e-mail',
      password: 'senha',
      date: 'data',
      description: 'descrição',
      amount: 'valor',
      type: 'tipo',
      file: 'arquivo',
    }

    return `O campo ${fieldNames[requiredField[1]] || requiredField[1]} é obrigatório.`
  }
  if (message.startsWith('The email field must be a valid email address')) {
    return 'Informe um endereço de e-mail válido.'
  }
  if (message.startsWith('The date field must match the format Y-m-d')) {
    return 'A data deve estar no formato AAAA-MM-DD.'
  }
  if (message.startsWith('The selected type is invalid')) {
    return 'Selecione receita ou despesa.'
  }
  if (message.startsWith('The description field must not be greater than 255 characters')) {
    return 'A descrição deve ter no máximo 255 caracteres.'
  }
  if (message.startsWith('The name field must not be greater than 255 characters')) {
    return 'O nome deve ter no máximo 255 caracteres.'
  }
  if (message.startsWith('The email has already been taken')) {
    return 'Este e-mail já está cadastrado.'
  }
  if (message.startsWith('The password field must be at least')) {
    return 'A senha deve ter pelo menos 8 caracteres.'
  }
  if (message.startsWith('The password field confirmation does not match')) {
    return 'A confirmação da senha não corresponde.'
  }
  if (message.startsWith('The file field')) {
    return 'Selecione um arquivo CSV válido de até 10 MB.'
  }
  if (message.startsWith('The date to field must be a date after or equal to date from')) {
    return 'A data final deve ser igual ou posterior à data inicial.'
  }
  if (message.startsWith('The amount field')) {
    return 'Informe um valor positivo com até duas casas decimais.'
  }
  if (message.startsWith('The type field')) {
    return 'Selecione receita ou despesa.'
  }

  return message
}

export function errorMessage(error: unknown, fallback: string): string {
  if (!axios.isAxiosError<{ message?: string; errors?: Record<string, string[]> }>(error)) {
    return fallback
  }

  const response = error.response?.data
  const firstFieldError = response?.errors ? Object.values(response.errors).flat()[0] : undefined
  const message = firstFieldError || response?.message

  if (!message) return fallback
  return translateApiMessage(message)
}
