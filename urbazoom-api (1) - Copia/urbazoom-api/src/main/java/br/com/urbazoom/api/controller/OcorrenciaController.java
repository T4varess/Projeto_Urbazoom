package br.com.urbazoom.api.controller;

import br.com.urbazoom.api.dto.AtualizarStatusRequest;
import br.com.urbazoom.api.dto.ContagemDTO;
import br.com.urbazoom.api.enums.Prioridade;
import br.com.urbazoom.api.model.HistoricoOcorrencia;
import br.com.urbazoom.api.model.Ocorrencia;
import br.com.urbazoom.api.model.StatusOcorrencia;
import br.com.urbazoom.api.repository.Contagem;
import br.com.urbazoom.api.repository.HistoricoOcorrenciaRepository;
import br.com.urbazoom.api.repository.OcorrenciaRepository;
import br.com.urbazoom.api.repository.StatusOcorrenciaRepository;
import br.com.urbazoom.api.repository.UsuarioRepository;
import io.swagger.v3.oas.annotations.Operation;
import io.swagger.v3.oas.annotations.tags.Tag;
import jakarta.validation.Valid;
import org.springframework.format.annotation.DateTimeFormat;
import org.springframework.http.HttpStatus;
import org.springframework.transaction.annotation.Transactional;
import org.springframework.web.bind.annotation.GetMapping;
import org.springframework.web.bind.annotation.PatchMapping;
import org.springframework.web.bind.annotation.PathVariable;
import org.springframework.web.bind.annotation.RequestBody;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.RestController;
import org.springframework.web.server.ResponseStatusException;

import java.time.LocalDate;
import java.time.LocalDateTime;
import java.util.List;

@RestController
@RequestMapping("/ocorrencias")
@Tag(name = "Ocorrencias")
public class OcorrenciaController extends CrudController<Ocorrencia> {

    private final OcorrenciaRepository ocorrenciaRepository;
    private final StatusOcorrenciaRepository statusRepository;
    private final UsuarioRepository usuarioRepository;
    private final HistoricoOcorrenciaRepository historicoRepository;

    public OcorrenciaController(OcorrenciaRepository ocorrenciaRepository,
                                StatusOcorrenciaRepository statusRepository,
                                UsuarioRepository usuarioRepository,
                                HistoricoOcorrenciaRepository historicoRepository) {
        super(ocorrenciaRepository);
        this.ocorrenciaRepository = ocorrenciaRepository;
        this.statusRepository = statusRepository;
        this.usuarioRepository = usuarioRepository;
        this.historicoRepository = historicoRepository;
    }

    @Operation(summary = "Busca uma ocorrencia pelo protocolo (ex.: URB-2038)")
    @GetMapping("/protocolo/{protocolo}")
    public Ocorrencia buscarPorProtocolo(@PathVariable String protocolo) {
        return ocorrenciaRepository.findByProtocolo(protocolo)
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Protocolo nao encontrado"));
    }

    @Operation(summary = "Filtra por status, categoria, orgao e/ou prioridade (todos opcionais)")
    @GetMapping("/filtro")
    public List<Ocorrencia> filtrar(@RequestParam(required = false) Long statusId,
                                    @RequestParam(required = false) Long categoriaId,
                                    @RequestParam(required = false) Long orgaoId,
                                    @RequestParam(required = false) Prioridade prioridade) {
        return ocorrenciaRepository.filtrar(statusId, categoriaId, orgaoId, prioridade);
    }

    @Operation(summary = "Lista as ocorrencias abertas por um cidadao")
    @GetMapping("/usuario/{usuarioId}")
    public List<Ocorrencia> listarPorUsuario(@PathVariable Long usuarioId) {
        return ocorrenciaRepository.findByUsuarioIdOrderByAbertaEmDesc(usuarioId);
    }

    @Operation(summary = "Lista as ocorrencias abertas em um periodo (datas no formato AAAA-MM-DD)")
    @GetMapping("/periodo")
    public List<Ocorrencia> listarPorPeriodo(
            @RequestParam @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate inicio,
            @RequestParam @DateTimeFormat(iso = DateTimeFormat.ISO.DATE) LocalDate fim) {
        LocalDateTime de = inicio.atStartOfDay();
        LocalDateTime ate = fim.plusDays(1).atStartOfDay().minusNanos(1);
        return ocorrenciaRepository.findByAbertaEmBetweenOrderByAbertaEmDesc(de, ate);
    }

    @Operation(summary = "Busca por texto no titulo ou na localizacao")
    @GetMapping("/busca")
    public List<Ocorrencia> buscarPorTexto(@RequestParam String texto) {
        return ocorrenciaRepository
                .findByTituloContainingIgnoreCaseOrLocalizacaoContainingIgnoreCase(texto, texto);
    }

    @Operation(summary = "Atualiza o status e registra o evento no historico da ocorrencia")
    @PatchMapping("/{id}/status")
    @Transactional
    public Ocorrencia atualizarStatus(@PathVariable Long id,
                                      @Valid @RequestBody AtualizarStatusRequest req) {
        Ocorrencia ocorrencia = ocorrenciaRepository.findById(id).orElseThrow(() -> naoEncontrado(id));
        StatusOcorrencia novoStatus = statusRepository.findById(req.statusId())
                .orElseThrow(() -> new ResponseStatusException(HttpStatus.NOT_FOUND, "Status nao encontrado"));

        ocorrencia.setStatus(novoStatus);
        ocorrencia.setResolvidaEm("resolvida".equals(novoStatus.getSlug()) ? LocalDateTime.now() : null);
        ocorrenciaRepository.save(ocorrencia);

        HistoricoOcorrencia historico = new HistoricoOcorrencia();
        historico.setOcorrencia(ocorrencia);
        historico.setStatus(novoStatus);
        historico.setMensagem(req.mensagem() != null && !req.mensagem().isBlank()
                ? req.mensagem()
                : "Status atualizado para " + novoStatus.getNome() + ".");
        if (req.usuarioId() != null) {
            historico.setUsuario(usuarioRepository.findById(req.usuarioId()).orElse(null));
        }
        historicoRepository.save(historico);

        return ocorrencia;
    }

    @Operation(summary = "Relatorio: total de ocorrencias por status")
    @GetMapping("/relatorio/por-status")
    public List<ContagemDTO> relatorioPorStatus() {
        return converter(ocorrenciaRepository.contarPorStatus());
    }

    @Operation(summary = "Relatorio: total de ocorrencias por categoria")
    @GetMapping("/relatorio/por-categoria")
    public List<ContagemDTO> relatorioPorCategoria() {
        return converter(ocorrenciaRepository.contarPorCategoria());
    }

    @Operation(summary = "Relatorio: total de ocorrencias por orgao")
    @GetMapping("/relatorio/por-orgao")
    public List<ContagemDTO> relatorioPorOrgao() {
        return converter(ocorrenciaRepository.contarPorOrgao());
    }

    private List<ContagemDTO> converter(List<Contagem> contagens) {
        return contagens.stream().map(c -> new ContagemDTO(c.getNome(), c.getTotal())).toList();
    }
}
