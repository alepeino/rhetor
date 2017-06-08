<?php

if (! \Illuminate\Support\Arr::hasMacro('merge')) {
    \Illuminate\Support\Arr::macro('merge', function ($first, ...$rest) {
        return array_reduce($rest, function ($result, $item) {
            $item = $item instanceof \Illuminate\Support\Collection ? $item->all() : $item;

            foreach ($item as $key => $value) {
                if (\Illuminate\Support\Arr::exists($result, $key) &&
                    \Illuminate\Support\Arr::accessible($result[$key]) &&
                    \Illuminate\Support\Arr::accessible($value)
                ) {
                    $result[$key] = \Illuminate\Support\Arr::merge($result[$key], $value);
                } else {
                    $result[$key] = $value;
                }
            }

            return $result;
        }, $first instanceof \Illuminate\Support\Collection ? $first->all() : $first);
    });
}
