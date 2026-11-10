package br.com.urbazoom.api.repository;

/** Projecao usada nas consultas de contagem (relatorios). */
public interface Contagem {
    String getNome();

    Long getTotal();
}
