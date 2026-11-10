package br.com.urbazoom.api.model;

import br.com.urbazoom.api.enums.Prioridade;
import jakarta.persistence.Column;
import jakarta.persistence.Entity;
import jakarta.persistence.EnumType;
import jakarta.persistence.Enumerated;
import jakarta.persistence.JoinColumn;
import jakarta.persistence.ManyToOne;
import jakarta.persistence.PrePersist;
import jakarta.persistence.PreUpdate;
import jakarta.persistence.Table;
import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import lombok.Getter;
import lombok.Setter;

import java.time.LocalDateTime;
import java.util.UUID;

@Entity
@Table(name = "ocorrencias")
@Getter
@Setter
public class Ocorrencia extends BaseEntity {

    // Se nao for informado, e gerado automaticamente (ex.: URB-3F9A1C2B)
    @Column(length = 20, nullable = false, unique = true, updatable = false)
    private String protocolo;

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "usuario_id")
    private Usuario usuario;

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "categoria_id")
    private Categoria categoria;

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "orgao_id")
    private Orgao orgao;

    @NotBlank
    @Column(length = 180, nullable = false)
    private String titulo;

    @NotBlank
    @Column(length = 2000, nullable = false)
    private String descricao;

    @NotBlank
    @Column(length = 255, nullable = false)
    private String localizacao;

    private Double lat;

    private Double lng;

    @NotNull
    @Enumerated(EnumType.STRING)
    @Column(nullable = false)
    private Prioridade prioridade = Prioridade.MEDIA;

    @NotNull
    @ManyToOne(optional = false)
    @JoinColumn(name = "status_id")
    private StatusOcorrencia status;

    @Column(length = 255)
    private String anexoPath;

    @Column(updatable = false)
    private LocalDateTime abertaEm;

    private LocalDateTime atualizadaEm;

    private LocalDateTime resolvidaEm;

    @PrePersist
    void aoCriar() {
        abertaEm = LocalDateTime.now();
        atualizadaEm = abertaEm;
        if (protocolo == null || protocolo.isBlank()) {
            protocolo = "URB-" + UUID.randomUUID().toString().substring(0, 8).toUpperCase();
        }
    }

    @PreUpdate
    void aoAtualizar() {
        atualizadaEm = LocalDateTime.now();
    }
}
