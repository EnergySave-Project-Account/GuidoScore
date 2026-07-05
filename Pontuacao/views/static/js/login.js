import { request, BASE_URL } from './lib/lib.js';

document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector(".score-form")

    form.addEventListener("submit", async (e) => {
        e.preventDefault();

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        const user = data["username"];
        const password = data["password"];

        const res = await request(BASE_URL + "/login", "POST", {
            body: {
                username: user,
                password: password
            }
        });

        if(res["redirect"]){
            window.location.href = "/pontuacao/home?token=" + res["url_token"]
        }
        
        console.log(res)
    })
})