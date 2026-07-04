window.addEventListener('DOMContentLoaded', async () => {
    if (await requireLogin()) carregarDashboard();
});

async function carregarDashboard() {
    const response = await fetch('php/produtos.php');
    const data = await response.json();
    const stats = data.stats || {};

    document.getElementById('totalProdutos').textContent = stats.total || 0;
    document.getElementById('ideia').textContent = stats.ideia || 0;
    document.getElementById('teste').textContent = stats.teste || 0;
    document.getElementById('lancados').textContent = stats.lancado || 0;

    renderRecentes(data.produtos || []);
    renderFunilResumo(stats);
}

function renderRecentes(produtos) {
    const container = document.getElementById('produtosLista');
    if (!produtos.length) {
        container.innerHTML = '<div class="empty-state">Nenhum produto cadastrado.</div>';
        return;
    }

    container.innerHTML = produtos.slice(0, 6).map(produto => `
        <article class="recent-item">
            <h3>${escapeHtml(produto.nome)}</h3>
            <div class="meta-line">
                ${statusPill(produto.status)}
                <span>${escapeHtml(produto.categoria)}</span>
                <span>${escapeHtml(produto.responsavel || 'Sem responsável')}</span>
                <span>${formatDate(produto.data_atualizacao || produto.data_criacao)}</span>
            </div>
        </article>
    `).join('');
}

function renderFunilResumo(stats) {
    const container = document.getElementById('funilResumo');
    const total = Math.max(Number(stats.total || 0), 1);
    container.innerHTML = STATUS_ORDER.map(status => {
        const value = Number(stats[status] || 0);
        return `
            <div class="bar-row">
                <div class="bar-label"><span>${statusLabel(status)}</span><span>${value}</span></div>
                <div class="bar-track"><div class="bar-fill" style="width:${(value / total) * 100}%"></div></div>
            </div>
        `;
    }).join('');
}
