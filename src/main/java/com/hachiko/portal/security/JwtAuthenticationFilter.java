package com.hachiko.portal.security;

import com.hachiko.portal.service.ITokenBlacklistService;
import jakarta.servlet.FilterChain;
import jakarta.servlet.ServletException;
import jakarta.servlet.http.HttpServletRequest;
import jakarta.servlet.http.HttpServletResponse;
import org.springframework.security.authentication.UsernamePasswordAuthenticationToken;
import org.springframework.security.core.authority.SimpleGrantedAuthority;
import org.springframework.security.core.context.SecurityContextHolder;
import org.springframework.stereotype.Component;
import org.springframework.web.filter.OncePerRequestFilter;

import java.io.IOException;
import java.util.List;

/**
 * Filtro JWT — se ejecuta una sola vez por request.
 *
 * Valida el token y verifica que no haya sido revocado (blacklist).
 * Si el token es inválido o está en la blacklist, no establece contexto
 * y Spring Security bloqueará el acceso con 401.
 *
 * Principio DIP: depende de IJwtTokenProvider (interfaz), no de JwtTokenProvider.
 */
@Component
public class JwtAuthenticationFilter extends OncePerRequestFilter {

    private final IJwtTokenProvider jwtTokenProvider;
    private final ITokenBlacklistService tokenBlacklistService;

    public JwtAuthenticationFilter(IJwtTokenProvider jwtTokenProvider,
                                   ITokenBlacklistService tokenBlacklistService) {
        this.jwtTokenProvider = jwtTokenProvider;
        this.tokenBlacklistService = tokenBlacklistService;
    }

    @Override
    protected void doFilterInternal(HttpServletRequest request,
                                    HttpServletResponse response,
                                    FilterChain filterChain)
            throws ServletException, IOException {

        String token = extractToken(request);

        if (token != null
                && jwtTokenProvider.validateToken(token)
                && !tokenBlacklistService.isBlacklisted(token)) {

            Integer userId = jwtTokenProvider.getUserIdFromToken(token);
            String role = jwtTokenProvider.getRoleFromToken(token);

            // Prefijo ROLE_ requerido por Spring Security para hasRole()
            List<SimpleGrantedAuthority> authorities =
                    List.of(new SimpleGrantedAuthority("ROLE_" + role));

            UsernamePasswordAuthenticationToken authentication =
                    new UsernamePasswordAuthenticationToken(userId, null, authorities);

            SecurityContextHolder.getContext().setAuthentication(authentication);
        }

        filterChain.doFilter(request, response);
    }

    /**
     * Extrae el token del header Authorization (formato: "Bearer <token>").
     *
     * @param request HTTP request entrante
     * @return token sin el prefijo "Bearer ", o null si no está presente
     */
    private String extractToken(HttpServletRequest request) {
        String header = request.getHeader("Authorization");
        if (header != null && header.startsWith("Bearer ")) {
            return header.substring(7);
        }
        return null;
    }
}
