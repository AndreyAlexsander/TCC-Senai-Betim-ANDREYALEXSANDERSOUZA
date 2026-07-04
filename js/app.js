const STATUS = {
    ideia: 'Ideia',
    desenvolvimento: 'Desenvolvimento',
    teste: 'Teste',
    aprovacao: 'Aprovação',
    lancado: 'Lançado',
    arquivado: 'Arquivado'
};

const STATUS_ORDER = ['ideia', 'desenvolvimento', 'teste', 'aprovacao', 'lancado', 'arquivado'];

function escapeHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, char => ({
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    }[char]));
}

function statusLabel(status) {
    return STATUS[status] || status || 'Sem status';
}

function statusPill(status) {
    return `<span class="status-pill status-${escapeHtml(status)}">${escapeHtml(statusLabel(status))}</span>`;
}

function formatDate(value) {
    if (!value) return '-';
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? '-' : date.toLocaleDateString('pt-BR');
}

function formatCurrency(value) {
    const number = Number(value || 0);
    return number.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 2600);
}

async function requireLogin() {
    const response = await fetch('php/verificar_login.php');
    const data = await response.json();
    if (!data.logado) {
        window.location.href = 'index.html';
        return null;
    }
    const userName = document.getElementById('userNome');
    if (userName) userName.textContent = data.nome;
    return data;
}

function debounce(fn, wait = 250) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => fn(...args), wait);
    };
}
