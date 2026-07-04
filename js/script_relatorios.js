let graficoRelatorio = null;
let relatorioAtual = { produtos: [], stats: {} };

window.addEventListener('DOMContentLoaded', async () => {
    if (await requireLogin()) gerarRelatorio();
});

async function gerarRelatorio() {
    const params = new URLSearchParams({
        dataInicio: document.getElementById('dataInicio').value,
        dataFim: document.getElementById('dataFim').value,
        status: document.getElementById('filtroRelatorio').value
    });

    const response = await fetch(`php/relatorios.php?${params.toString()}`);
    relatorioAtual = await response.json();
    atualizarResumo(relatorioAtual.stats || {});
    atualizarTabela(relatorioAtual.produtos || []);
    renderGrafico(relatorioAtual.stats || {});
}

function atualizarResumo(stats) {
    document.getElementById('totalRelatorio').textContent = stats.total || 0;
    document.getElementById('progressoRelatorio').textContent = Number(stats.desenvolvimento || 0) + Number(stats.teste || 0) + Number(stats.aprovacao || 0);
    document.getElementById('concluidosRelatorio').textContent = stats.lancado || 0;
    document.getElementById('potencialRelatorio').textContent = formatCurrency(stats.potencial || 0);
}

function atualizarTabela(produtos) {
    const tbody = document.querySelector('#tabelaRelatorio tbody');
    if (!produtos.length) {
        tbody.innerHTML = '<tr><td colspan="5">Nenhum resultado encontrado.</td></tr>';
        return;
    }

    tbody.innerHTML = produtos.map(produto => `
        <tr>
            <td>${escapeHtml(produto.nome)}</td>
            <td>${statusPill(produto.status)}</td>
            <td>${escapeHtml(produto.categoria)}</td>
            <td>${escapeHtml(produto.responsavel || '-')}</td>
            <td>${formatCurrency(produto.potencial_receita)}</td>
        </tr>
    `).join('');
}

function renderGrafico(stats) {
    const ctx = document.getElementById('graficoRelatorio');
    if (graficoRelatorio) graficoRelatorio.destroy();
    graficoRelatorio = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: STATUS_ORDER.map(statusLabel),
            datasets: [{
                label: 'Produtos',
                data: STATUS_ORDER.map(status => Number(stats[status] || 0)),
                backgroundColor: ['#b45309', '#0e7490', '#ca8a04', '#4f46e5', '#15803d', '#64748b']
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
        }
    });
}

function exportarPDF() {
    if (!window.jspdf) {
        alert('Biblioteca de PDF não carregada.');
        return;
    }
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF();
    doc.setFontSize(18);
    doc.text('Relatorio KAION', 14, 18);
    doc.setFontSize(10);
    doc.text(`Gerado em ${new Date().toLocaleDateString('pt-BR')}`, 14, 27);
    doc.text(`Total: ${relatorioAtual.stats.total || 0}`, 14, 38);
    doc.text(`Potencial: ${formatCurrency(relatorioAtual.stats.potencial || 0)}`, 14, 45);

    let y = 58;
    relatorioAtual.produtos.slice(0, 28).forEach(produto => {
        doc.text(`${produto.nome} | ${statusLabel(produto.status)} | ${formatCurrency(produto.potencial_receita)}`.substring(0, 92), 14, y);
        y += 7;
    });
    doc.save(`relatorio-kaion-${Date.now()}.pdf`);
}

function exportarExcel() {
    const params = new URLSearchParams({
        dataInicio: document.getElementById('dataInicio').value,
        dataFim: document.getElementById('dataFim').value,
        status: document.getElementById('filtroRelatorio').value
    });
    window.location.href = `php/exportar_excel.php?${params.toString()}`;
}
