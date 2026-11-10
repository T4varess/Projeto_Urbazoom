package br.com.urbazoom.api.dto;

import jakarta.validation.constraints.NotNull;

public record AtualizarStatusRequest(
        @NotNull Long statusId,
        String mensagem,
        Long usuarioId) {
}
