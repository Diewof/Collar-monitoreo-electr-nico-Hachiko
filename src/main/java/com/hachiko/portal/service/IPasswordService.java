package com.hachiko.portal.service;

/**
 * Contrato para el servicio de hashing y verificación de contraseñas.
 *
 * Principio DIP: LoginService y RegisterService dependen de esta interfaz,
 * no de BCryptPasswordEncoder directamente. Si en el futuro se cambia el
 * algoritmo de hash, solo cambia la implementación concreta.
 *
 * Principio SRP: esta interfaz (y su implementación) solo se ocupa de
 * operaciones criptográficas sobre contraseñas — no decide cuándo hashear
 * ni valida reglas de negocio.
 */
public interface IPasswordService {

    /**
     * Genera el hash seguro de una contraseña en texto plano.
     *
     * @param rawPassword contraseña en texto plano; nunca debe almacenarse directamente
     * @return hash BCrypt listo para persistir en la base de datos
     */
    String encode(String rawPassword);

    /**
     * Verifica que una contraseña en texto plano coincide con su hash almacenado.
     * Usado en el flujo de login antes de otorgar acceso.
     *
     * @param rawPassword      contraseña ingresada por el usuario
     * @param encodedPassword  hash almacenado en la base de datos
     * @return {@code true} si coinciden; {@code false} si no coinciden o los parámetros son nulos
     */
    boolean matches(String rawPassword, String encodedPassword);
}
