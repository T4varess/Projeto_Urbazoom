package br.com.urbazoom.api.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;

@Entity
@Table(name = "status_ocorrencia")
@Getter
@Setter
public class StatusOcorrencia extends BaseEntity {

    @NotBlank
    @Column(length = 50, nullable = false)
    private String nome;

    @NotBlank
    @Column(length = 50, nullable = false, unique = true)
    private String slug;

    @NotNull
    @Column(nullable = false)
    private Integer ordemExibicao;

    @Column(length = 30)
    private String classeBadge;
}
