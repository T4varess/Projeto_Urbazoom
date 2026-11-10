package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.Categoria;
import br.com.urbazoom.api.repository.CategoriaRepository;
import io.swagger.v3.oas.annotations.tags.Tag;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RestController;

import java.util.List;

@RestController
@RequestMapping("/categorias")
@Tag(name = "Categorias")
public class CategoriaController extends CrudController<Categoria> {

    private final CategoriaRepository categoriaRepository;

    public CategoriaController(CategoriaRepository categoriaRepository) {
        super(categoriaRepository);
        this.categoriaRepository = categoriaRepository;
    }

    @GetMapping("/orgao/{orgaoId}")
    public List<Categoria> listarPorOrgao(@PathVariable Long orgaoId) {
        return categoriaRepository.findByOrgaoId(orgaoId);
    }
}
