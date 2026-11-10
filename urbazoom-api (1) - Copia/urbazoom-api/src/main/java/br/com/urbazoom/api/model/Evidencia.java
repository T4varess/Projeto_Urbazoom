package br.com.urbazoom.api.model;

import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.PrePersist;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;
import org.hibernate.annotations.OnDelete;
import org.hibernate.annotations.OnDeleteAction;

import java.time.LocalDateTime;

@Entity
@Table(name = "evidencias")
@Getter
@Setter
public class Evidencia extends BaseEntity {

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "ocorrencia_id")
    @OnDelete(action = OnDeleteAction.CASCADE)
    private Ocorrencia ocorrencia;

    @NotBlank
    @Column(length = 255, nullable = false)
    private String nomeOriginal;

    @NotBlank
    @Column(length = 255, nullable = false)
    private String nomeArquivo;

    @NotBlank
    @Column(length = 100, nullable = false)
    private String tipoMime;

    private Integer tamanho = 0;

    @Column(updatable = false)
    private LocalDateTime enviadoEm;

    @PrePersist
    void aoCriar() {
        enviadoEm = LocalDateTime.now();
    }
}
