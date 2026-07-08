window.addEventListener('DOMContentLoaded', async () => {
    if (await requireLogin()) carregarDashboard();
});

async function carregarDashboard() {
    const resposta = await fetch('php/produtos.php');
    const dados = await resposta.json();
    const resumo = dados.stats || {};

    document.getElementById('totalProdutos').textContent = resumo.total || 0;
    document.getElementById('ideia').textContent = resumo.ideia || 0;
    document.getElementById('teste').textContent = resumo.teste || 0;
    document.getElementById('lancados').textContent = resumo.lancado || 0;

    renderRecentes(dados.produtos || []);
    renderFunilResumo(resumo);
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

function renderFunilResumo(resumo) {
    const container = document.getElementById('funilResumo');
    const total = Math.max(Number(resumo.total || 0), 1);

    container.innerHTML = STATUS_ORDER.map(status => {
        const quantidade = Number(resumo[status] || 0);
        return `
            <div class="bar-row">
                <div class="bar-label"><span>${statusLabel(status)}</span><span>${quantidade}</span></div>
                <div class="bar-track"><div class="bar-fill" style="width:${(quantidade / total) * 100}%"></div></div>
            </div>
        `;
    }).join('');
}
