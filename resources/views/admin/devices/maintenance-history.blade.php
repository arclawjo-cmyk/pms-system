@extends('admin.layouts.app')

@section('title', 'Maintenance History')
@section('page_title', 'Maintenance History')

@section('content')
<div class="space-y-5">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900">Maintenance History</h1>
            <p class="mt-1 text-sm text-gray-500">
                {{ $device->type?->name ?? 'Device' }} |
                Property #: {{ $device->property_number }}
            </p>
        </div>

        <a
            href="{{ route('admin.devices.index') }}"
            class="rounded-xl bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200"
        >
            Back
        </a>
    </div>

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50 text-left">
                <tr>
                    <th class="px-4 py-3 font-semibold text-gray-700">Date</th>
                    <th class="px-4 py-3 font-semibold text-gray-700">Type</th>
                    <th class="px-4 py-3 font-semibold text-gray-700">Remarks</th>
                    <th class="px-4 py-3 font-semibold text-gray-700">Checked By</th>
                    <th class="px-4 py-3 font-semibold text-gray-700">Recorded At</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @forelse($records as $record)
                    <tr>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $record->maintenance_date?->format('M d, Y') }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $record->maintenance_type }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $record->remarks ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $record->checkedBy?->name ?? $record->checkedBy?->email ?? '-' }}
                        </td>

                        <td class="px-4 py-3 text-gray-700">
                            {{ $record->created_at?->format('M d, Y h:i A') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            No maintenance records yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection