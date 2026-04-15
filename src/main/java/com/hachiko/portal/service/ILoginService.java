package com.hachiko.portal.service;

import com.hachiko.portal.dto.auth.LoginRequest;
import com.hachiko.portal.dto.auth.LoginResponse;

/**
 * Contrato para el flujo de autenticación de usuarios.
 * Módulo: Autenticación.
 *
 * Orquesta: verificación de email, lock check, verificación de contraseña,
 * actualización de lastLogin, limpieza de intentos fallidos, y flag de perfil incompleto.
 *
 * Principio SRP: solo responsable del flujo de login. No registra usuarios
 * ni gestiona tokens de recuperación de contraseña.
 */
public interface ILoginService {

    /**
     * Autentica a un usuario y retorna los datos de sesión.
     *
     * Flujo (1:1 con authmodel.php):
     *  1. Buscar usuario por email → AuthenticationException si no existe
     *  2. Verificar bloqueo (email + ip) → AccountLockedException si bloqueado
     *  3. Verificar contraseña BCrypt → si falla: recordFailedAttempt + AuthenticationException
     *  4. updateLastLogin a now()
     *  5. clearAttempts (email + ip)
     *  6. Verificar si tiene perfil propietario → flag requiresProfileCompletion
     *  7. Retornar LoginResponse
     *
     * @param request   DTO con email y password
     * @param ipAddress IP del cliente (extraída por el controlador en Etapa 5)
     * @return LoginResponse con userId, email, role y requiresProfileCompletion
     * @throws com.hachiko.portal.exception.AccountLockedException si la cuenta está bloqueada
     * @throws com.hachiko.portal.exception.AuthenticationException si las credenciales son incorrectas
     */
    LoginResponse login(LoginRequest request, String ipAddress);
}
