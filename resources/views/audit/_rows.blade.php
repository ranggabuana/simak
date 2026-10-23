@forelse($logs as $log)
<tr class="hover:bg-slate-50 transition">
    <td class="py-3.5 px-4 font-mono text-slate-500 whitespace-nowrap">
        {{ $log->created_at->format('d/m/Y H:i:s') }}
    </td>
    <td class="py-3.5 px-4">
        <span class="font-bold text-slate-900 block">{{ $log->user_name }}</span>
        <span class="text-[10px] text-slate-400 font-semibold uppercase">Role: {{ $log->user_role }}</span>
    </td>
    <td class="py-3.5 px-4 text-center">
        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold uppercase
            {{ $log->action === 'CREATE' ? 'bg-emerald-100 text-emerald-800' : ($log->action === 'UPDATE' ? 'bg-blue-100 text-blue-800' : ($log->action === 'DELETE' ? 'bg-rose-100 text-rose-800' : ($log->action === 'IMPORT' ? 'bg-purple-100 text-purple-800' : 'bg-amber-100 text-amber-800'))) }}">
            {{ $log->action }}
        </span>
    </td>
    <td class="py-3.5 px-4 font-semibold text-slate-700 capitalize">
        {{ $log->module }}
    </td>
    <td class="py-3.5 px-4 text-slate-600">
        {{ $log->description }}
    </td>
    <td class="py-3.5 px-4 font-mono text-slate-400 text-[11px]">
        {{ $log->ip_address ?: '-' }}
    </td>
</tr>
@empty
<tr>
    <td colspan="6" class="text-center py-10 text-slate-400">Belum ada catatan aktivitas audit trail.</td>
</tr>
@endforelse
