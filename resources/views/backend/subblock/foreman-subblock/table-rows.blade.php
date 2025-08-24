@forelse($foremanSubBlocks as $key => $foremanSubBlock)
<tr>
    <td class="text-center">{{ $foremanSubBlocks->firstItem() + $key }}</td>
    <td class="text-center">{{ $foremanSubBlock->kode_petak }}</td>
    <td class="text-center">{{ $foremanSubBlock->divisi }}</td>
    <td class="text-center">{{ $foremanSubBlock->kode_mandor }}</td>
    <td class="text-center">{{ $foremanSubBlock->nama_mandor }}</td>
    <td class="text-center">
        <div class="btn-group">
            <a href="{{ route('foreman-sub-blocks.edit', $foremanSubBlock->id) }}"
               class="btn btn-sm btn-warning"
               data-bs-toggle="tooltip"
               title="Edit">
                <i class="fas fa-edit"></i>
            </a>
            <form action="{{ route('foreman-sub-blocks.destroy', $foremanSubBlock->id) }}"
                  method="POST"
                  class="d-inline"
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger" data-bs-toggle="tooltip" title="Hapus">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center">Tidak ada data</td>
</tr>
@endforecase
