export const BASE_URL = "";

function getCsrfDataFromPage() {
    if (window.csrfData) {
        return window.csrfData;
    }

    const container = document.getElementById('csrf-data');
    if (container) {
        const value = container.getAttribute('data-csrf');
        if (value) {
            try {
                const parsed = JSON.parse(value);
                window.csrfData = parsed;
                window.csrfToken = parsed?.csrf_token ?? '';
                window.csrfGenerated = parsed?.generated ?? '';
                return parsed;
            } catch {
                return null;
            }
        }
    }

    return null;
}

export function getCsrfHeaders(extraHeaders = {}) {
    const csrfData = getCsrfDataFromPage();
    const csrfToken = window.csrfToken ?? null;
    const csrfGenerated = window.csrfGenerated ?? null;
    const headers = { ...(extraHeaders || {}) };

    if (csrfData) {
        headers["X-CSRF-Header"] = JSON.stringify(csrfData);
    } else if (csrfToken && csrfGenerated) {
        headers["X-CSRF-Header"] = JSON.stringify({
            csrf_token: csrfToken,
            generated: csrfGenerated
        });
    }

    return headers;
}

export async function request(url, method = "GET", options = {}) {
    try {
        const { body: rawBody, headers: customHeaders, ...rest } = options;
        const headers = {
            "Content-Type": "application/json",
            ...getCsrfHeaders(customHeaders),
            ...(customHeaders || {})
        };

        let body = rawBody;
        if (body && typeof body === 'object' && !(body instanceof FormData)) {
            body = JSON.stringify(body);
        }

        const response = await fetch(url, {
            method,
            credentials: "include",
            body,
            ...rest,
            headers,
        });

        let data;
        const text = await response.text();

        try {
            data = JSON.parse(text);
        } 
        catch {
            data = `=====================================\nErro Interno do Servidor\n=====================================\n${text}`;
        }

        if (!response.ok) {
            return {
                success: false,
                message: data?.message || data || "Erro na requisição"
            };
        }
        
        return data;
    } 
    
    catch (error) {
        return {
            success: false,
            message: `Erro de conexão com o servidor. \n${error}`
        };
    }
}