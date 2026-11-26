<style>
body { background: #f4f6f9; }
.report-container { background: white; padding: 1.5rem; min-height: 100vh; }
.report-container .table th, .report-container .table td { font-size: 13px; vertical-align: middle; }
.report-summary { border: 1px solid #ddd; padding: 12px; margin-bottom: 12px; border-radius: 6px; }
@page { margin: .3in .5in .5in .5in; }
@media print {
    body { background: white; }
    .report-container { padding: 0; }
    .extra-remove { display: none !important; }
    .table-responsive { overflow: visible !important; }
    tr { break-inside: avoid; }
}
</style>
