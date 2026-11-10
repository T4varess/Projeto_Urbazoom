package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.Orgao;
import br.com.urbazoom.api.repository.OrgaoRepository;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

@RestController
@RequestMapping("/orgaos")
@Tag(name = "Orgaos")
public class OrgaoController extends CrudController<Orgao> {

    public OrgaoController(OrgaoRepository repository) {
        super(repository);
    }
}
