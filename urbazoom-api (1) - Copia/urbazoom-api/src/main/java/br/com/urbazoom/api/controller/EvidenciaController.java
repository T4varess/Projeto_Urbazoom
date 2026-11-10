package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.Evidencia;
import br.com.urbazoom.api.repository.EvidenciaRepository;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/evidencias")
@Tag(name = "Evidencias")
public class EvidenciaController extends CrudController<Evidencia> {

    private final EvidenciaRepository evidenciaRepository;

    public EvidenciaController(EvidenciaRepository evidenciaRepository) {
        super(evidenciaRepository);
        this.evidenciaRepository = evidenciaRepository;
    }

    @GetMapping("/ocorrencia/{ocorrenciaId}")
    public List<Evidencia> listarPorOcorrencia(@PathVariable Long ocorrenciaId) {
        return evidenciaRepository.findByOcorrenciaId(ocorrenciaId);
    }
}
