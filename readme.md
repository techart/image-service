# Сервис по обработке изображений

Сервис представляет собой адаптер для обработки, сохранения, получения и удаления картинок.

Стандарнно имеет 2 менеджров по работе с изображениями:
- ```InterventionImageManager```, работает с библиотекой ```intervention/image``` поддерживает драйвера ```imagick```  и ```gd```
- ```GumletImageResizeManager```, работает с библиотекой```gumlet/php-image-resize``` поддерживает только ```gd``` драйвер

Менеджеры представляют собой класс адаптер к библиотекам, для написания собстевнного менеджера необходимо реализовать интерфейс ```ImageManagerContract```

Так же есть 2 класса по работе с файловой системой ```StandardStorage``` и ```LaravelStorage```(для проектов на laravel), при желании можно написать собственный, главное не забыть реализовать интерфейс ```StorageContract```

Классы, реализующие ```StorageContract```, представляют собой управление файловой системой, что необходимо для создания директорий, получения пути до файла и т.д

# Возможности
- Конвертировать изображения в разные форматы, зависит от выбранного менеджера.
- Изменять размер изображений, методы так же зависят от выбранного менеджера.
- Удалять созданные изображаения

# Как использовать
Для использования необходимо выбрать класс менеджреа и класс по работе с файловой системой, 
задать конфигурационный массив и получить сущность класса ImageService.

# Конфиг
Конфиг представляет собой массив, ограничевающий работу сервиса в рамках доуступных методов, размеров, качества и форматов изображений.

Может иметь следущие ключи: ```'resize' | 'format' | 'method' | 'quality'```

Ключ ```quality``` явяется не обязательным

Ключ ```resize``` моежт быть как массивом, содрежащим доступные размеры, например:
```
[
    '200x200',
    '200x0',
    '0x200',
    '1200x369'
]
```
А так же может быть строкой, со значением ```*```, что будет убирать ограничение на доступные размеры

# Пример работы

## Объект ```\Techart\ImageService\Service```
Основной класс управления, предаставляющий доступ к объекту изменения изображения и к объекту работы с уже полученными картинками.
```php
$manager = new \Techart\ImageService\Managers\InterventionImageManager();
$storage = \Techart\ImageService\Storages\StandardStorage::getInstance();
$config = [
    'sizes' => '*',
    'format' => [
        'webp',
        'jpg',
        'jpeg',
        'png',
        'gif',
    ],
    'methods' => [
        'resize',
        'crop',
        'fit',
    ]
];

$service = \Techart\ImageService\Service::getInstance($manager, $storage, $config);
```

## Объект ```\Techart\ImageService\Storage```
Представляет собой класс для получения и удаления созданых изображений.

Данный класс возвращает метод ```storage``` объекта ```\Techart\ImageService\Service```. В метод необходимо передать оносительный путь оригинальной картинки

Класс имеет следующий набор методов
- ```haveModifyImages``` - возвращает ```true``` или ```false``` в зависимости от того, есть ли модифицированные копии оригинально изображения
- ```getOriginalImage``` - возвращает массив, содержащий пути всех модифицированных копий оригинально изображения
- ```delete``` - удаляет все модифицированные копии оригинально изображения, и само оригинальное изображение. Для того, что бы не удалить оригинальное изображение передайте ```false``` в качестве аргумента при вызове метода


## Объект ```\Techart\ImageService\Processor```
Представляет собой класс, реализующий манипуляции с изображениями, через него так же можно задавать параметры для манипуляций.

Данный класс возвращает метод ```modify``` объекта ```\Techart\ImageService\Service```.

Выполнение манипуляций происходит после вызова метода ```process```, который вернет сущность класса ```\Techart\ImageService\Paths```, который предоставляет доступ к
информации, url и пути расположения оригинального и нового изображения.

## Объект ```\Techart\ImageService\Paths```
Возвращается в результате работы метода ```process``` объекта ```\Techart\ImageService\Processor```.
Имеет следующий набор методов
- ```getInfo``` - возвращает массив с данными об изображении, имеет следующую структуру:
```
    [
        'dirname' => 'Директория картинки',
        'basename' => 'Имя картинки вместе с раширением',
        'extension' => 'Расширение картинки',
        'filename' => 'Имя картинки',
        'real_path' => 'Абсолютный путь картинки',
        'path' => 'Относительный путь картинки',
        'mime' => 'Расширение картинки в формате mime',
        'url' => 'URL по которому картинка будет доступена',
        'size' => [
            'w' => Ширина картинки,
            'h' => Высота картинки,
            'string' => Строка вида width="{Ширина}" height="{Высота}",
        ],
    ]
```
- ```getUrl``` - возвращает URL по которому картинка будет доступена
- ```getPath``` - возвращает абсолютный путь картинки

Так же существуют аналогичные методы для оригинального изображения, например, ```getOriginalUrl```

**Пример работы непосредственно с изображением**

## Пример работы:
> Важно, путь до оригнальной картинки должен быть относительный

### Модификация картинок
Пример работы, где параметры для обрабокти устанавливаются с помощью методов класса ```\Techart\ImageService\Processor```:

```php
    $imageUrl = $service->modify('путь до оригинальной картинки')
        ->setQuality(95)
        ->setSizes('800x600')
        ->setFormat('webp')
        ->setMethod('fit')
        ->process()
        ->getUrl()
```

Пример работы, где параметры для обрабокти передаются массивом с следущими ключами:
- resize - ресайз
- quality - качество
- format - формат
- method - метод, по умолчанию resize

```php
$imageUrl = $service->modify('путь до оригинальной картинки', [
    'resize' => '400x0',
    'format' => 'webp',
    'quality' => 80,
    'method' => 'fit'
])->process()->getUrl()
```

Пример работы, где параметры для обрабокти передаются строкой
- /r - ресайз
- /q - качество
- /f - формат
- /m - метод, по умолчанию resize(подходит в 90% случаев)

```php
$imageUrl = $service->setup('путь до оригинальной картинки', '/r/640x480/r/f/webp/f/q/80/q/m/fit/m')
    ->process()
    ->getUrl();
```


### Удаление изображений

Не забудьте, при необходимости(удаление записи в бд, или обновления картинки и пр.), удалить изображения

Для этого есть объект ```\Techart\ImageService\Storage``` и его метод ```delete()```, который удаляет все изображения связанные с оригиналом(включая оригинал).

Что бы не удалить оригинал передайте ```false``` первым аргументом

**Пример:**
```php
$originalImage = 'путь до оригинальной картинки';

$storage = $service->storage($originalImage);

if ($storage->haveModifyImages()) {
    $storage->delete(false);
}
```