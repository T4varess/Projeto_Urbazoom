package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.HistoricoOcorrencia;
import br.com.urbazoom.api.repository.HistoricoOcorrenciaRepository;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/historicos")
@Tag(name = "Historico de ocorrencias")
public class HistoricoOcorrenciaController extends CrudController<HistoricoOcorrencia> {

    private final HistoricoOcorrenciaRepository historicoRepository;

    public HistoricoOcorrenciaController(HistoricoOcorrenciaRepository historicoRepository) {
        super(historicoRepository);
        this.historicoRepository = historicoRepository;
    }

    @GetMapping("/ocorrencia/{ocorrenciaId}")
    public List<HistoricoOcorrencia> linhaDoTempo(@PathVariable Long ocorrenciaId) {
        return historicoRepository.findByOcorrenciaIdOrderByCriadoEmAsc(ocorrenciaId);
    }
}
