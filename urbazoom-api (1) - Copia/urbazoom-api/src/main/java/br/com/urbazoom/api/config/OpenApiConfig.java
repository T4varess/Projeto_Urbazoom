package br.com.urbazoom.api.config;

import io.swagger.v3.oas.models.OpenAPI;
import io.swagger.v3.oas.models.info.Info;
import org.springframework.context.annotation.Bean;
import org.springframework.context.annotation.Configuration;

@Configuration
public class OpenApiConfig {

    @Bean
    public OpenAPI urbazoomOpenAPI() {
        return new OpenAPI().info(new Info()
                .title("UrbaZoom API")
                .version("1.0.0")
                .description("API REST para registro e acompanhamento de ocorrencias urbanas "
                        + "(buracos, iluminacao, lixo, vazamentos e sinalizacao)."));
    }
}
