const messages = {
    credenciais: 'Email ou senha incorretos.',
    servidor: 'Não foi possível concluir a operação agora.'
};

window.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const key = params.get('error') || params.get('success');
    const target = document.getElementById('mensagemLogin');

    if (!target || !messages[key]) return;
    target.textContent = messages[key];
    target.className = `form-message ${params.get('error') ? 'error' : 'success'}`;
});
