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
@Table(name = "historico_ocorrencias")
@Getter
@Setter
public class HistoricoOcorrencia extends BaseEntity {

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "ocorrencia_id")
    @OnDelete(action = OnDeleteAction.CASCADE)
    private Ocorrencia ocorrencia;

    @ManyToOne
    @JoinColumn(name = "usuario_id")
    @OnDelete(action = OnDeleteAction.SET_NULL)
    private Usuario usuario;

    @ManyToOne
    @JoinColumn(name = "status_id")
    @OnDelete(action = OnDeleteAction.SET_NULL)
    private StatusOcorrencia status;

    @NotBlank
    @Column(length = 2000, nullable = false)
    private String mensagem;

    @Column(updatable = false)
    private LocalDateTime criadoEm;

    @PrePersist
    void aoCriar() {
        criadoEm = LocalDateTime.now();
    }
}
