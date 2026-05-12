package com.hachiko.portal.controller;

import com.fasterxml.jackson.databind.ObjectMapper;
import com.hachiko.portal.AbstractIntegrationTest;
import com.hachiko.portal.dto.auth.LoginRequest;
import com.hachiko.portal.dto.auth.RegisterRequest;
import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.Test;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.http.MediaType;
import org.springframework.test.web.servlet.MockMvc;
import org.springframework.test.web.servlet.setup.MockMvcBuilders;
import org.springframework.web.context.WebApplicationContext;

import static org.springframework.test.web.servlet.request.MockMvcRequestBuilders.post;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.jsonPath;
import static org.springframework.test.web.servlet.result.MockMvcResultMatchers.status;

class AuthControllerIT extends AbstractIntegrationTest {

    @Autowired
    WebApplicationContext wac;

    @Autowired
    ObjectMapper objectMapper;

    MockMvc mockMvc;

    @BeforeEach
    void setup() {
        mockMvc = MockMvcBuilders
                .webAppContextSetup(wac)
                .build();
    }

    @Test
    void register_withValidData_returns201() throws Exception {
        RegisterRequest request = RegisterRequest.builder()
                .email("nuevo@test.com")
                .password("Password123")
                .confirmPassword("Password123")
                .build();

        mockMvc.perform(post("/api/auth/register")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isCreated())
                .andExpect(jsonPath("$.email").value("nuevo@test.com"));
    }

    @Test
    void login_withValidCredentials_returns200WithToken() throws Exception {
        // Registrar primero
        RegisterRequest reg = RegisterRequest.builder()
                .email("login@test.com")
                .password("Password123")
                .confirmPassword("Password123")
                .build();
        mockMvc.perform(post("/api/auth/register")
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(reg)));

        // Login
        LoginRequest request = LoginRequest.builder()
                .email("login@test.com")
                .password("Password123")
                .build();

        mockMvc.perform(post("/api/auth/login")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isOk())
                .andExpect(jsonPath("$.token").isNotEmpty())
                .andExpect(jsonPath("$.email").value("login@test.com"));
    }

    @Test
    void login_withWrongPassword_returns401() throws Exception {
        // Registrar primero
        RegisterRequest reg = RegisterRequest.builder()
                .email("bloqueado@test.com")
                .password("Password123")
                .confirmPassword("Password123")
                .build();
        mockMvc.perform(post("/api/auth/register")
                .contentType(MediaType.APPLICATION_JSON)
                .content(objectMapper.writeValueAsString(reg)));

        // Login con contraseña incorrecta
        LoginRequest request = LoginRequest.builder()
                .email("bloqueado@test.com")
                .password("ContraseniaWrong")
                .build();

        mockMvc.perform(post("/api/auth/login")
                        .contentType(MediaType.APPLICATION_JSON)
                        .content(objectMapper.writeValueAsString(request)))
                .andExpect(status().isUnauthorized());
    }
}
