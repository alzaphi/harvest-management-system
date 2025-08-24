@if ($subblocks->hasPages())
    <div class="flex justify-center mt-6">
        <nav class="inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            @php
                $currentPage = $subblocks->currentPage();
                $lastPage = $subblocks->lastPage();
                $window = 2; // Number of pages to show on each side of current page

                // Calculate start and end pages
                $start = max(1, $currentPage - $window);
                $end = min($lastPage, $currentPage + $window);

                // Adjust if we're near the start
                if ($start <= 1) {
                    $start = 1;
                    $end = min($lastPage, $start + ($window * 2));
                }

                // Adjust if we're near the end
                if ($end >= $lastPage) {
                    $end = $lastPage;
                    $start = max(1, $end - ($window * 2));
                }

                // Ensure we always show 5 pages if possible
                $pageRange = $end - $start + 1;
                if ($pageRange < 5 && $lastPage > 4) {
                    if ($start === 1) {
                        $end = min($lastPage, 5);
                    } else {
                        $start = max(1, $end - 4);
                    }
                }
            @endphp

            {{-- Previous Page Button --}}
            <a href="{{ $subblocks->previousPageUrl() }}"
               class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ $currentPage == 1 ? 'opacity-50 cursor-not-allowed' : '' }}"
               {{ $currentPage == 1 ? 'tabindex="-1" aria-disabled="true"' : '' }}
               data-page="{{ $currentPage - 1 }}">
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>

            {{-- First Page --}}
            @if($start > 1)
                <a href="{{ $subblocks->url(1) }}"
                   class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                   data-page="1">
                    1
                </a>
                @if($start > 2)
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                    </span>
                @endif
            @endif

            {{-- Page Numbers --}}
            @for ($i = $start; $i <= $end; $i++)
                <a href="{{ $subblocks->url($i) }}"
                   class="relative inline-flex items-center px-4 py-2 border text-sm font-medium {{ $i == $currentPage ? 'z-10 bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50' }}"
                   data-page="{{ $i }}">
                    {{ $i }}
                </a>
            @endfor

            {{-- Last Page --}}
            @if($end < $lastPage)
                @if($end < $lastPage - 1)
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        ...
                    </span>
                @endif
                <a href="{{ $subblocks->url($lastPage) }}"
                   class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                   data-page="{{ $lastPage }}">
                    {{ $lastPage }}
                </a>
            @endif

            {{-- Next Page Button --}}
            <a href="{{ $subblocks->nextPageUrl() }}"
               class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 {{ $currentPage == $lastPage ? 'opacity-50 cursor-not-allowed' : '' }}"
               {{ $currentPage == $lastPage ? 'tabindex="-1" aria-disabled="true"' : '' }}
               data-page="{{ $currentPage + 1 }}">
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        </nav>
    </div>
@endif
