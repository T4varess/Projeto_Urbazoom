package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.model.BaseEntity;
import jakarta.persistence.EntityManager;
import jakarta.persistence.PersistenceContext;
import jakarta.validation.Valid;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.http.HttpStatus;
import org.springframework.web.bind.annotation.DeleteMapping;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.PostMapping;
import org.springframework.web.bind.annotation.PutMapping;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.ResponseStatus;
import org.springframework.web.server.ResponseStatusException;

import java.util.List;

/**
 * CRUD generico: cada controller concreto herda os 5 endpoints basicos
 * (listar, buscar por id, criar, atualizar, excluir) e adiciona os seus especificos.
 */
public abstract class CrudController<T extends BaseEntity> {

    protected final JpaRepository<T, Long> repository;

    @PersistenceContext
    protected EntityManager em;

    protected CrudController(JpaRepository<T, Long> repository) {
        this.repository = repository;
    }

    @GetMapping
    public List<T> listar() {
        return repository.findAll();
    }

    @GetMapping("/{id}")
    public T buscarPorId(@PathVariable Long id) {
        return repository.findById(id).orElseThrow(() -> naoEncontrado(id));
    }

    @PostMapping
    @ResponseStatus(HttpStatus.CREATED)
    public T criar(@Valid @RequestBody T entidade) {
        entidade.setId(null);
        T salvo = repository.saveAndFlush(entidade);
        return recarregar(salvo.getId());
    }

    @PutMapping("/{id}")
    public T atualizar(@PathVariable Long id, @Valid @RequestBody T entidade) {
        if (!repository.existsById(id)) {
            throw naoEncontrado(id);
        }
        entidade.setId(id);
        repository.saveAndFlush(entidade);
        return recarregar(id);
    }

    @DeleteMapping("/{id}")
    @ResponseStatus(HttpStatus.NO_CONTENT)
    public void excluir(@PathVariable Long id) {
        if (!repository.existsById(id)) {
            throw naoEncontrado(id);
        }
        repository.deleteById(id);
    }

    // Limpa o cache da sessao e le de novo, para a resposta trazer os objetos relacionados completos
    private T recarregar(Long id) {
        em.clear();
        return repository.findById(id).orElseThrow(() -> naoEncontrado(id));
    }

    protected ResponseStatusException naoEncontrado(Long id) {
        return new ResponseStatusException(HttpStatus.NOT_FOUND, "Registro " + id + " nao encontrado");
    }
}
