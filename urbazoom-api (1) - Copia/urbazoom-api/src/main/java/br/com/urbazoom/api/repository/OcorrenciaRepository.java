package br.com.urbazoom.api.repository;

import br.com.urbazoom.api.enums.Prioridade;
import br.com.urbazoom.api.model.Ocorrencia;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;

import java.time.LocalDateTime;
import java.util.List;
import java.util.Optional;

public interface OcorrenciaRepository extends JpaRepository<Ocorrencia, Long> {

    Optional<Ocorrencia> findByProtocolo(String protocolo);

    List<Ocorrencia> findByUsuarioIdOrderByAbertaEmDesc(Long usuarioId);

    List<Ocorrencia> findByAbertaEmBetweenOrderByAbertaEmDesc(LocalDateTime inicio, LocalDateTime fim);

    List<Ocorrencia> findByTituloContainingIgnoreCaseOrLocalizacaoContainingIgnoreCase(String titulo, String localizacao);

    // Filtro combinado: qualquer parametro nao informado e ignorado
    @Query("""
            select o from Ocorrencia o
            where (:statusId is null or o.status.id = :statusId)
              and (:categoriaId is null or o.categoria.id = :categoriaId)
              and (:orgaoId is null or o.orgao.id = :orgaoId)
              and (:prioridade is null or o.prioridade = :prioridade)
            order by o.abertaEm desc
            """)
    List<Ocorrencia> filtrar(@Param("statusId") Long statusId,
                             @Param("categoriaId") Long categoriaId,
                             @Param("orgaoId") Long orgaoId,
                             @Param("prioridade") Prioridade prioridade);

    @Query("select o.status.nome as nome, count(o) as total from Ocorrencia o group by o.status.nome")
    List<Contagem> contarPorStatus();

    @Query("select o.categoria.nome as nome, count(o) as total from Ocorrencia o group by o.categoria.nome")
    List<Contagem> contarPorCategoria();

    @Query("select o.orgao.nome as nome, count(o) as total from Ocorrencia o group by o.orgao.nome")
    List<Contagem> contarPorOrgao();
}
