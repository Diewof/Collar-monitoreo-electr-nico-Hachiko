// ── Auth ──────────────────────────────────────────────────────────────────────
export interface LoginRequest {
  email: string
  password: string
}

export interface LoginResponse {
  userId: number
  email: string
  role: 'ADMIN' | 'USER'
  token: string
  requiresProfileCompletion: boolean
}

export interface RegisterRequest {
  email: string
  password: string
  confirmPassword: string
}

export interface PasswordResetRequest {
  email: string
}

export interface NewPasswordRequest {
  token: string
  password: string
  confirmPassword: string
}

export interface UsuarioDTO {
  id: number
  email: string
  role: 'ADMIN' | 'USER'
  createdAt: string
  lastLogin: string | null
}

// ── Propietario ───────────────────────────────────────────────────────────────
export interface ResidenciaDTO {
  ciudadId: number
  ciudad: string
  departamentoId: number
  departamento: string
  paisId: number
  pais: string
  direccion: string
}

export interface PropietarioDTO {
  propietarioId: number
  usuarioId: number
  primerNombre: string
  segundoNombre: string | null
  apellido: string
  segundoApellido: string | null
  telefono: string
  email: string
  planId: number
  planNombre: string
  residencia: ResidenciaDTO
}

export interface CreatePropietarioRequest {
  usuarioId: number
  primerNombre: string
  segundoNombre?: string
  apellido: string
  segundoApellido?: string
  telefono: string
  direccion: string
  ciudadId: number
  planId: number
}

export interface UpdatePropietarioRequest {
  propietarioId: number
  primerNombre: string
  segundoNombre?: string
  apellido: string
  segundoApellido?: string
  telefono: string
  direccion: string
  ciudadId: number
  planId: number
}

// ── Mascotas ─────────────────────────────────────────────────────────────────
export interface MascotaDTO {
  perroId: number
  nombre: string
  fechaNacimiento: string
  peso: number
  genero: 'M' | 'F'
  esterilizado: boolean
  razaId: number
  nombreRaza: string
  propietarioId: number
}

export interface CreateMascotaRequest {
  nombre: string
  fechaNacimiento: string
  peso: number
  genero: 'M' | 'F'
  esterilizado: boolean
  razaId: number
}

export interface UpdateMascotaRequest extends CreateMascotaRequest {}

// ── Referencia ────────────────────────────────────────────────────────────────
export interface PaisDTO {
  paisId: number
  nombre: string
}

export interface DepartamentoDTO {
  departamentoId: number
  nombre: string
}

export interface CiudadDTO {
  ciudadId: number
  nombre: string
}

export interface RazaDTO {
  razaId: number
  nombre: string
}

export interface PlanDTO {
  planId: number
  nombre: string
  precio: number
  descripcion: string
}

// ── Admin ─────────────────────────────────────────────────────────────────────
export interface ActividadRecienteDTO {
  tipo: string
  descripcion: string
  fecha: string
}

export interface DashboardStatsDTO {
  totalUsuarios: number
  loginHoy: number
  intentosFallidos: number
  cuentasBloqueadas: number
  actividad: ActividadRecienteDTO[]
}

export interface UserDetailDTO {
  usuario: UsuarioDTO
  propietario: PropietarioDTO | null
  mascotas: MascotaDTO[]
}

// ── Errores ───────────────────────────────────────────────────────────────────
export interface ApiValidationError {
  field: string
  message: string
}

export interface ApiErrorResponse {
  status: number
  message: string
  errors?: ApiValidationError[]
}
