<?php 

    class Env {
        /**
         * Carrega variáveis do arquivo .env.
         *
         * Caso $fieldsToGet seja informado, apenas os campos especificados serão carregados.
         * Caso contrário, todas as variáveis do .env serão retornadas.
         *
         * Os valores podem ser utilizados para definição de constantes ou configuração da aplicação.
         *
         * @param array|null $fieldsToGet Lista de chaves do .env a serem carregadas (opcional)
         * @return array Retorna um array associativo no formato ['CHAVE' => valor]
         */

        public static function get($fieldsToGet = null) {
            $envPath = defined('BASE_DIR') ? BASE_DIR . '/.env' : null;

            if ($envPath === null || !file_exists($envPath)) {
                $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? null;
                $envPath = $documentRoot ? $documentRoot . '/.env' : null;
            }

            if ($envPath === null || !file_exists($envPath)) {
                $workspaceRoot = dirname(__DIR__, 3);
                $fallbackPath = $workspaceRoot . '/.env';

                if (file_exists($fallbackPath)) {
                    $envPath = $fallbackPath;
                }
            }

            if (!file_exists($envPath)) {
                throw new Exception('.env não encontrado');
            }

            $env = parse_ini_file($envPath, false, INI_SCANNER_RAW);

            if ($fieldsToGet === null) {
                $fields = [
                    "API_SECRET",
                    "API_KEY",
                    "DEBUG",
                    "ORIGIN",
                    "BASE_URL",
                    "BASE_URL_PATH",
                    "DBUSER",
                    "DBPASSWORD",
                    "DBNAME",
                    "DBHOST",
                    "DBPORT"
                ];
            } 
            
            else {
                $fields = is_array($fieldsToGet) ? $fieldsToGet : [$fieldsToGet];
            }

            $result = [];

            foreach ($fields as $field) {
                $value = $env[$field] ?? null;

                if ($value === 'true') $value = true;
                if ($value === 'false') $value = false;

                $result[$field] = $value;
            }

            return $result;
        }

    }