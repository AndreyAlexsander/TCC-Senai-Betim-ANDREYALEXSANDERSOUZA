const mensagens = {
    credenciais: 'Email ou senha incorretos.',
    servidor: 'Não foi possível concluir a operação agora.'
};

window.addEventListener('DOMContentLoaded', () => {
    const parametros = new URLSearchParams(window.location.search);
    const aviso = parametros.get('error') || parametros.get('success');
    const campoMensagem = document.getElementById('mensagemLogin');

    if (!campoMensagem || !mensagens[aviso]) return;

    campoMensagem.textContent = mensagens[aviso];
    campoMensagem.className = `form-message ${parametros.get('error') ? 'error' : 'success'}`;
});
