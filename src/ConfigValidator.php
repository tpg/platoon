<?php

declare(strict_types=1);

namespace TPG\Platoon;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ConfigValidator
{
    public function __construct(protected readonly array $config)
    {
        $this->validator = Validator::make($this->config, $this->rules());
    }

    public function validate(): bool
    {
        return $this->validator->passes();
    }

    public function errors(): array
    {
        return $this->validator->errors()->toArray();
    }

    protected function rules(): array
    {
        return [
            'repo' => [
                'required',
                'string',
            ],
            'targets' => [
                'required',
                'array',
                'min:1',
            ],
            'targets.common.*' => [
                'nullable',
            ],
            'targets.*' => [
                'array:host,port,username,root,php,composer,branch,migrate,assets,hooks,extra',
                'min:1',
            ],
            'targets.*.*' => Rule::forEach(function ($value, $attribute) {
                return $this->targetValidationRules($attribute);
            }),
        ];
    }

    protected function targetValidationRules(string $key): array
    {
        $prop = Str::of($key)->after('targets.')->after('.')->toString();

        return $this->targetRule($prop, $this->isCommon($key));
    }

    protected function targetRule(string $key, bool $isCommon = false): array
    {
        return Arr::get([
            'host' => [
                $isCommon ? 'nullable' : 'required',
                'string',
            ],
            'port' => [
                'nullable',
                'integer',
            ],
            'username' => [
                $isCommon ? 'nullable' : 'required',
                'string',
            ],
            'root' => [
                $isCommon ? 'nullable' : 'required',
                'string',
            ],
            'php' => [
                'nullable',
                'string',
            ],
            'composer' => [
                'nullable',
                'string',
            ],
            'branch' => [
                'nullable',
                'string',
            ],
            'migrate' => [
                'nullable',
                'boolean',
            ],
            'assets' => [
                'nullable',
                'array',
            ],
            'hooks' => [
                'nullable',
                'array',
            ],
            'hooks.*' => [
                'nullable',
                'array'
            ],
            'extra' => [
                'nullable',
                'array',
            ]
        ], $key);
    }

    protected function isCommon(string $key): bool
    {
        return Str::of($key)->after('.')->startsWith('common');
    }
}
