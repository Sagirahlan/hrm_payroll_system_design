<div class="mb-3">
    <form action="{{ $route }}" method="GET" class="row g-3">
        <div class="col-md-4">
            <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="filter" class="form-select">
                <option value="">All</option>
                @foreach ($filterOptions as $option)
                    <option value="{{ $option['value'] }}" {{ request('filter') == $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}" placeholder="Start Date">
        </div>
        <div class="col-md-2">
            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}" placeholder="End Date">
        </div>
        <div class="col-md-1">
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
        </div>
        @if ($additionalFilters ?? false)
            <div class="col-md-12">
                @foreach ($additionalFilters as $filter)
                    <div class="form-check form-check-inline">
                        <input type="checkbox" name="{{ $filter['name'] }}[]" id="{{ $filter['name'] }}_{{ $filter['value'] }}" value="{{ $filter['value'] }}" class="form-check-input" {{ in_array($filter['value'], request($filter['name'], [])) ? 'checked' : '' }}>
                        <label for="{{ $filter['name'] }}_{{ $filter['value'] }}" class="form-check-label">{{ $filter['label'] }}</label>
                    </div>
                @endforeach
            </div>
        @endif
    </form>
</div>