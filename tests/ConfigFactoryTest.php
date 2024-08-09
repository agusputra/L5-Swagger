<?php

namespace Tests;

use L5Swagger\Exceptions\L5SwaggerException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\TestDox;

#[TestDox('Configuration factory')]
class ConfigFactoryTest extends TestCase
{
    public function testItThrowsExceptionIfDocumentationConfigNotFound(): void
    {
        $config = config('l5-swagger');
        unset($config['documentations']['default']);
        config(['l5-swagger' => $config]);

        $this->expectException(\L5Swagger\Exceptions\L5SwaggerException::class);
        $this->expectExceptionMessage('Documentation config not found');

        $this->configFactory->documentationConfig();
    }

    /**
     * @throws L5SwaggerException
     * @dataProvider configDataProvider
     */
    #[DataProvider('configDataProvider')]
    public function testCanMergeConfigurationDeep(array $data, array $assert): void
    {
        config(['l5-swagger' => array_merge(
            $data,
            [
                'defaults' => [
                    'routes' => [
                        'api' => 'api/documentation',
                        'docs' => 'docs',
                    ],
                    'paths' => [
                        'docs' => 'docs/',
                        'docs_yaml' => 'docs.yaml',
                    ],
                    'proxy' => false,
                ],
            ]
        )]);

        $config = $this->configFactory->documentationConfig();

        $this->assertArraySimilar($config, $assert);
    }

    public static function configDataProvider(): \Iterator
    {
        yield [
            'data' => [
                'default' => 'v2',
                'documentations' => [
                    'v2' => [
                        'api' => [
                            'title' => 'Api V2',
                        ],
                        'paths' => [
                            'docs_json' => 'api-v2.json',
                        ],
                        'proxy' => true,
                    ],
                ],
            ],
            'assert' => [
                'api' => [
                    'title' => 'Api V2',
                ],
                'routes' => [
                    'api' => 'api/documentation',
                    'docs' => 'docs',
                ],
                'paths' => [
                    'docs_json' => 'api-v2.json',
                    'docs' => 'docs/',
                    'docs_yaml' => 'docs.yaml',
                ],
                'proxy' => true,
            ],
        ];
        yield [
            'data' => [
                'default' => 'v1',
                'documentations' => [
                    'v1' => [
                        'api' => [
                            'title' => 'Api V1',
                        ],
                        'routes' => [
                            'api' => 'api/v1',
                        ],
                        'paths' => [
                            'docs_json' => 'api-v1.json',
                        ],
                    ],
                ],
            ],
            'assert' => [
                'api' => [
                    'title' => 'Api V1',
                ],
                'routes' => [
                    'api' => 'api/v1',
                    'docs' => 'docs',
                ],
                'paths' => [
                    'docs_json' => 'api-v1.json',
                    'docs' => 'docs/',
                    'docs_yaml' => 'docs.yaml',
                ],
                'proxy' => false,
            ],
        ];
    }

    /**
     * Asserts that two associative arrays are similar.
     *
     * Both arrays must have the same indexes with identical values
     * without respect to key ordering
     */
    protected function assertArraySimilar(array $expected, array $array): void
    {
        $this->assertSame([], array_diff_key($array, $expected));

        foreach ($expected as $key => $value) {
            if (is_array($value)) {
                $this->assertArraySimilar($value, $array[$key]);
                continue;
            }

            $this->assertContains($value, $array);
        }
    }
}
