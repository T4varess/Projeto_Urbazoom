package br.com.urbazoom.api.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.Table;
import jakarta.validation.constraints.Email;
import jakarta.validation.constraints.NotBlank;
import lombok.Getter;
import lombok.Setter;

@Entity
@Table(name = "orgaos")
@Getter
@Setter
public class Orgao extends BaseEntity {

    @NotBlank
    @Column(length = 120, nullable = false)
    private String nome;

    @Column(length = 255)
    private String descricao;

    @Email
    @Column(length = 150)
    private String emailContato;

    private boolean ativo = true;
}
