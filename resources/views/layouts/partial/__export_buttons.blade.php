<div class="d-flex justify-content-end {{ isset($no_spacing) && $no_spacing ? '' : 'mb-4 border-bottom pb-3' }}">
    <a role="button" onclick="getprint('{{ $printAreaId ?? 'printArea' }}', '{{ $fileName ?? 'Report' }}')" class="btn btn-info bg-gradient-info btn-sm text-white extra-remove" title="Print">
        <i class="fa fa-print"></i>
    </a>
    <a role="button" onclick="shareReport('{{ $printAreaId ?? 'printArea' }}', '{{ $fileName ?? 'Report' }}')" id="shareBtn" class="btn btn-success bg-gradient-success btn-sm text-white extra-remove ml-2" title="Share via WhatsApp">
        <i class="fa fa-share-alt"></i>
    </a>
    <a role="button" onclick="downloadImage('{{ $printAreaId ?? 'printArea' }}', '{{ $fileName ?? 'Report' }}')" id="downloadBtn" class="btn btn-warning bg-gradient-warning btn-sm text-dark extra-remove ml-2" title="Download Image">
        <i class="fa fa-download"></i>
    </a>
    <a role="button" onclick="downloadPDF('{{ $printAreaId ?? 'printArea' }}', '{{ $fileName ?? 'Report' }}')" id="downloadPdfBtn" class="btn btn-danger bg-gradient-danger btn-sm text-white extra-remove ml-2" title="Download PDF">
        <i class="fa fa-file-pdf"></i>
    </a>
</div>
