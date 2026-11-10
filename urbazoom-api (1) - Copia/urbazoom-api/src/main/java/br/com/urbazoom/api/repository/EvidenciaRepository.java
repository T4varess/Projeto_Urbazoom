package br.com.urbazoom.api.repository;

import br.com.urbazoom.api.model.Evidencia;
import org.springframework.data.jpa.repository.JpaRepository;

import java.util.List;

public interface EvidenciaRepository extends JpaRepository<Evidencia, Long> {

    List<Evidencia> findByOcorrenciaId(Long ocorrenciaId);
}
