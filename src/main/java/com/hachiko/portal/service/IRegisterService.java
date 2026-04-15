package com.hachiko.portal.service;

import com.hachiko.portal.dto.auth.RegisterRequest;
import com.hachiko.portal.dto.usuario.UsuarioDTO;

/**
 * Contrato para el registro de nuevos usuarios.
 * Módulo: Autenticación.
 *
 * Orquesta: validación de campos, hashing de contraseña, persistencia y envío de email.
 *
 * Principio SRP: solo responsable del flujo de registro. No autentica ni
 * gestiona recuperación de contraseña.
 */
public interface IRegisterService {

    /**
     * Registra un nuevo usuario en el sistema.
     *
     * Flujo (1:1 con authmodel.php register()):
     *  1. Validar email + password con UserValidator.validateNewUser()
     *     → ValidationException si hay errores
     *  2. Verificar que password == confirmPassword
     *     → ValidationException("Las contraseñas no coinciden.") si difieren
     *  3. Hashear contraseña con IPasswordService.encode()
     *  4. Construir Usuario con role=USER y createdAt=now()
     *  5. usuarioRepository.save(usuario)
     *  6. emailService.send() — correo de bienvenida
     *  7. Retornar UsuarioDTO del usuario creado
     *
     * @param request DTO con email, password y confirmPassword
     * @return UsuarioDTO del usuario recién creado
     * @throws com.hachiko.portal.exception.ValidationException si validación falla
     * @throws com.hachiko.portal.exception.DuplicateResourceException si el email ya existe
     */
    UsuarioDTO register(RegisterRequest request);
}
