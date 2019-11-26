<h1 align="center"> bloomfilter </h1>

<p align="center"> redsi布隆过滤器.</p>


## Installing

```shell
$ composer require mu/bloomfilter -vvv
```

## Usage
使用场景：
- 判断ip是否需要拦截
- 判断用户是否为会员



```php
use Mu\Bloomfilter\BloomFilter;

# redis服务器
$redis_conf = [
    'host' => '127.0.0.1',
    'port' => 6379,
    'auth' => 'beauty',
    'timeout'  => 1,
    'database' =>0
];

$bloomfilter = new BloomFilter($redis_conf);
# 设置评论的空间
$bloomfilter->set_bucket('black_ips');
# 添加一个ip
$ip = '127.0.0.1';
$add_rs = $bloomfilter->add($ip);
# 批量添加
$ips = ['192.168.0.1','192.168.0.2'];
$adds_rs = $bloomfilter->multi_add($ips);

# 检查ip
$rs = $bloomfilter->exists($ip);

# 批量检查ip
$multi_rs = $bloomfilter->multi_exists($ips);


```

## Contributing

You can contribute in one of three ways:

1.  [胡超博客](http://imhuchao.com/1271.html).

## License

MIT