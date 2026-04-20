import { useState, type ChangeEvent } from 'react'

/* ── Validadores predefinidos ────────────────────────────────────────────── */
export const validators = {
  email: (v: string) =>
    /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v) ? null : 'Ingresa un email válido',

  required: (label: string) => (v: string) =>
    v.trim() ? null : `${label} es requerido`,

  minLength: (n: number) => (v: string) =>
    v.length >= n ? null : `Mínimo ${n} caracteres`,

  passwordMatch: (getOther: () => string) => (v: string) =>
    v === getOther() ? null : 'Las contraseñas no coinciden',
}

/* ── Hook useField ───────────────────────────────────────────────────────── */
interface FieldConfig {
  initialValue?: string
  validate: (value: string) => string | null
}

export function useField(config: FieldConfig) {
  const [value, setValue]     = useState(config.initialValue ?? '')
  const [touched, setTouched] = useState(false)

  const validationError = config.validate(value)
  const isValid         = validationError === null

  return {
    value,
    /** null = no mostrar aún (campo no tocado) */
    error:   touched ? validationError : null,
    isValid: touched ? isValid : null,
    /** Estado CSS: 'valid' | 'invalid' | '' */
    state:   touched ? (isValid ? 'valid' : 'invalid') : '',
    onChange: (e: ChangeEvent<HTMLInputElement>) => setValue(e.target.value),
    onBlur:  () => setTouched(true),
    /** Forzar touched (al intentar submit) */
    touch:   () => setTouched(true),
    reset:   () => { setValue(config.initialValue ?? ''); setTouched(false) },
  }
}
