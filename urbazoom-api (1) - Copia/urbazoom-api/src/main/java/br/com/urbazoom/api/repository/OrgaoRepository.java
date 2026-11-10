package br.com.urbazoom.api.repository;

import br.com.urbazoom.api.model.Orgao;
import org.springframework.data.jpa.repository.JpaRepository;

public interface OrgaoRepository extends JpaRepository<Orgao, Long> {
}
