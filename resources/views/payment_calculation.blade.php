@extends('layouts.app')

@section('content')
<main class="flex-1 bg-gray-100 relative p-4 overflow-auto">
    <section id="paymentCalculationPage"
             class="bg-white rounded shadow p-4 max-w-full overflow-x-auto">
        <h2 class="text-lg font-semibold mb-4 text-gray-800">REKAP SPB</h2>

        <!-- Search and Filter -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:space-x-4 mb-4 gap-2">
            <input type="text" id="searchVendor" placeholder="Search Vendor"
                   class="border border-gray-300 rounded px-3 py-1 text-xs w-full sm:w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-500" />

            <div class="flex items-center space-x-2 text-xs">
                <label for="filterMonth">Month:</label>
                <select id="filterMonth"
                        class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    @foreach (range(1, 12) as $month)
                        <option value="{{ str_pad($month, 2, '0', STR_PAD_LEFT) }}">
                            {{ DateTime::createFromFormat('!m', $month)->format('F') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center space-x-2 text-xs">
                <label for="filterYear">Year:</label>
                <select id="filterYear"
                        class="border border-gray-300 rounded px-2 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">All</option>
                    @foreach ([2023, 2024, 2025] as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Table -->
        <form id="paymentCalcForm">
            <div class="overflow-x-auto">
                <table class="min-w-full border border-gray-300 text-xs text-left text-gray-700">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="border border-gray-300 px-2 py-1 text-center w-6">
                                <input type="checkbox" id="selectAll" aria-label="Select all rows" />
                            </th>
                            <th class="border border-gray-300 px-2 py-1">Nomor</th>
                            <th class="border border-gray-300 px-2 py-1">Nama Vendor</th>
                            <th class="border border-gray-300 px-2 py-1">Nomor SPB</th>
                            <th class="border border-gray-300 px-2 py-1">Petak</th>
                            <th class="border border-gray-300 px-2 py-1">Jenis</th>
                            <th class="border border-gray-300 px-2 py-1">Kode Unit</th>
                            <th class="border border-gray-300 px-2 py-1">Tanggal Timbang</th>
                            <th class="border border-gray-300 px-2 py-1">Bruto</th>
                            <th class="border border-gray-300 px-2 py-1">Tanggal Tara</th>
                            <th class="border border-gray-300 px-2 py-1">Tara</th>
                            <th class="border border-gray-300 px-2 py-1">Tonase 1</th>
                            <th class="border border-gray-300 px-2 py-1">Sortase</th>
                            <th class="border border-gray-300 px-2 py-1">Tonase Final</th>
                            <th class="border border-gray-300 px-2 py-1">Zona</th>
                        </tr>
                    </thead>
                    <tbody id="paymentCalcTableBody">
                        {{-- Kosong dulu --}}
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="flex justify-between items-center mt-4 text-sm">
                <div class="text-gray-600">Showing 0 of 0 entries</div>
                <div class="space-x-2">
                    <button type="button"
                            class="bg-gray-300 text-gray-700 px-3 py-1 rounded hover:bg-gray-400 disabled:opacity-50"
                            disabled>
                        Previous
                    </button>
                    <button type="button"
                            class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 disabled:opacity-50"
                            disabled>
                        Next
                    </button>
                </div>
            </div>

            <!-- Floating Action -->
            <div class="flex justify-end mt-6">
                <div class="flex gap-2">
                    <button type="button"
                            id="backToCalcBtn"
                            class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition"
                            style="display: none;">
                        Back
                    </button>
                    <button type="submit"
                            class="bg-blue-700 text-white px-4 py-2 rounded text-sm hover:bg-blue-800 transition">
                        Calculate Payment
                    </button>
                </div>
            </div>
        </form>
    </section>
</main>
@endsection
