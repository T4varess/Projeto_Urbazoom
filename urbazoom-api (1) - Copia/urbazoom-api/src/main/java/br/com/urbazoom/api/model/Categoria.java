package br.com.urbazoom.api.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;

@Entity
@Table(name = "categorias")
@Getter
@Setter
public class Categoria extends BaseEntity {

    @NotBlank
    @Column(length = 100, nullable = false, unique = true)
    private String nome;

    @Column(length = 255)
    private String descricao;

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "orgao_id")
    private Orgao orgao;

    private boolean ativa = true;
}
