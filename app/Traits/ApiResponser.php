<?php
namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser
{
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    protected function showAll(Collection $c, $code = 200)
    {
        if ($c->isEmpty()) {
            return $this->successResponse(['data' => $c], $code);
        }

        $transformer = $c->first()->transformer;

        $c = $this->filterData($c, $transformer);
        $c = $this->sortData($c, $transformer);
        $c = $this->paginate($c);
        $c = $this->transformData($c, $transformer);
        $c = $this->cacheResponse($c);

        return $this->successResponse($c, $code);
    }

    protected function showOne(Model $m, $code = 200)
    {
        $transformer = $m->transformer;
        $m = $this->transformData($m, $transformer);

        return $this->successResponse($m, $code);
    }

    protected function showMessage($m, $code = 200)
    {
        return $this->successResponse(['data' => $m], $code);
    }

    protected function filterData(Collection $c, $transformer)
    {
        foreach (request()->query() as $query => $value) {
            $attribute = $transformer::originalAttribute($query);

            if (isset($attribute, $value)) {
                $c = $c->where($attribute, $value);
            }
        }

        return $c;
    }

    protected function sortData(Collection $c, $transformer)
    {
        if (request()->has('sort_by')) {
            $attribute = $transformer::originalAttribute(request()->sort_by);
            $c = $c->sortBy($attribute);
        }
        return $c;
    }

    protected function paginate(Collection $collection)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:50',
        ];
        Validator::validate(request()->all(), $rules);
        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 15;
        if (request()->has('per_page')) {
            $perPage = (int) request()->per_page;
        }
        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();
        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);
        $paginated->appends(request()->all());
        return $paginated;
    }

    protected function transformData($data, $transformer)
    {
        $transformation = fractal($data, new $transformer);
        return $transformation->toArray();
    }

    protected function cacheResponse($data)
    {
        $url = request()->url();
		$queryParams = request()->query();
		ksort($queryParams);
		$queryString = http_build_query($queryParams);
		$fullUrl = "{$url}?{$queryString}";
		return Cache::remember($fullUrl, 30/60, function() use($data) {
			return $data;
		});

    }
}

?>
