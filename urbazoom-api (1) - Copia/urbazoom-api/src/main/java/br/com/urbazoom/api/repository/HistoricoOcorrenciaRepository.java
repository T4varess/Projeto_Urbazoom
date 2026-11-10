package br.com.urbazoom.api.repository;

import br.com.urbazoom.api.model.HistoricoOcorrencia;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface HistoricoOcorrenciaRepository extends JpaRepository<HistoricoOcorrencia, Long> {

    List<HistoricoOcorrencia> findByOcorrenciaIdOrderByCriadoEmAsc(Long ocorrenciaId);
}
