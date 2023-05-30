# Сервис по обработки изображений

Сервис представляет собой адаптер для обработки, сохранения, получения и удаления картинок.

Стандарнно имеет 2 менеджров по работе с изображениями:
- ```InterventionImageManager```, работает с библиотекой ```intervention/image``` поддерживает драйвера ```imagick```  и ```gd```
- ```GumletImageResizeManager```, работает с библиотекой```gumlet/php-image-resize``` поддерживает только ```gd``` драйвер

Менеджеры представляют собой класс адаптер к библиотекам, для написания собстевнного менеджера необходимо реализовать интерфейс ```ImageManagerContract```

Так же есть 2 класса по работе с файловой системой ```StandardStorage``` (прверялся пока только в bitrix) и ```LaravelStorage```, при желании можно написать собственный, главное не забыть реализовать интерфейс ```StorageContract```

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

**Получаем сущность основного класса:**
```
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

$service = \Techart\ImageService\ImageService::getInstance($manager, $storage, $config);
```

**Пример работы непосредственно с изображением**


> Важно, путь до оригнальной картинки должен быть относительный, каким мы его получаем из бд

Пример работы, где параметры для обрабокти передаются массивом с следущими ключами:
- resize - ресайз
- quality - качество
- format - формат
- method - метод, по умолчанию resize(подходит в 90% случаев)

```
$image = $service->setup('путь до оригинальной картинки', [
    'resize' => '400x0',
    'format' => 'webp',
    'quality' => 80,
    'method' => 'fit'
])->process()->getUrl();
```

Пример работы, где параметры для обрабокти передаются строкой
- /r - ресайз
- /q - качество
- /f - формат
- /m - метод, по умолчанию resize(подходит в 90% случаев)

```
$image = $service->setup('путь до оригинальной картинки', '/r/640x480/r/f/webp/f/q/80/q/m/fit/m')
    ->process()
    ->getUrl();
```


# Удаление изображений

Не забудьте, при необходимости(удаление записи в бд, или обновления картинки и пр.), удалить изображения

Для этого есть метод ```delete()```, который удаляет все изображения связанные с оригиналом(включая оригинал).

Что бы не удалить оригинал передайте ```false``` первым аргументом

**Пример:**
```
$service->setup('путь до оригинальной картинки')
    ->delete(false);
```