const mostrarSenha = document.getElementById('mostrarSenha');
const senha = document.getElementById('senha');
const eyeIcon = document.getElementById('eyeIcon');

// CORRECAO: impede erro de JavaScript caso este arquivo seja reutilizado sem o formulario de login.
if (mostrarSenha && senha && eyeIcon) {
    mostrarSenha.addEventListener('click', () => {
        const senhaVisivel = senha.type === 'password';
        senha.type = senhaVisivel ? 'text' : 'password';
        eyeIcon.classList.toggle('bi-eye', !senhaVisivel);
        eyeIcon.classList.toggle('bi-eye-slash', senhaVisivel);
        mostrarSenha.setAttribute('aria-label', senhaVisivel ? 'Ocultar senha' : 'Mostrar senha');
    });
}

// CORRECAO: exibe o retorno de credenciais invalidas apos o redirecionamento do backend.
const mensagemErro = document.getElementById('mensagemErro');
if (mensagemErro && new URLSearchParams(window.location.search).get('erro')) {
    mensagemErro.textContent = 'Usuario ou senha invalidos.';
    mensagemErro.classList.remove('d-none');
}
