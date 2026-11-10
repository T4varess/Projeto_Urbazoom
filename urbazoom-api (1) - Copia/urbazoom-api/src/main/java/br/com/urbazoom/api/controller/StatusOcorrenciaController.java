package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.StatusOcorrencia;
import br.com.urbazoom.api.repository.StatusOcorrenciaRepository;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/status")
@Tag(name = "Status de ocorrencia")
public class StatusOcorrenciaController extends CrudController<StatusOcorrencia> {

    public StatusOcorrenciaController(StatusOcorrenciaRepository repository) {
        super(repository);
    }
}
