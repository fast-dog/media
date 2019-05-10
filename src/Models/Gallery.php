<?php
namespace FastDog\Media\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Учет загруженных файлов
 *
 * В большей степени подразумевается загрузка изображений, но допускается загрузка архивов и презентаций в различных форматах
 *
 * @package FastDog\Media\Entity
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 *
 */
class Gallery extends Model
{
    /**
     * Название
     * @const string
     */
    const NAME = 'name';

    /**
     * Сортировка
     * @const string
     */
    const SORT = 'sort';

    /**
     * Дополнительные параметры
     * @const string
     */
    const DATA = 'data';

    /**
     * Название таблицы
     *
     * @var string $table
     */
    protected $table = 'media_gallery';

    /**
     * Массив полей автозаполнения
     *
     * @var array $fillable
     */
    protected $fillable = [self::NAME, self::DATA, self::SORT];
    /**
     * Допустимые расширения файлов
     *
     * Массив расширений для генерации превью
     *
     * @var array $allowExt
     */
    protected static $allowExt = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * Получение превью изображения
     *
     * Метод кэширует созданное превью в директории размещения файла
     * с оригинальным именем в вложенной директории .{$x}x{$y}
     *
     * @param string $_image путь к файлу
     * @param int $x размер по горизонтали (ширина)
     * @param null $y размер по вертикале (высота)
     * @return array
     */
    public static function getPhotoThumb($_image, $x = 300, $y = null)
    {
        $exp = explode('/', $_image);
        $fileName = end($exp);
        $ext = explode('.', $fileName);
        $ext = strtolower(end($ext));
        if ($_SERVER['DOCUMENT_ROOT'] == '') {
            $_SERVER['DOCUMENT_ROOT'] = dirname(dirname(dirname(dirname(__FILE__) . '../') . '../') . '../') . DIRECTORY_SEPARATOR . 'public';
        }

        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $_image)) {
            if (!in_array($ext, self::$allowExt)) {
                $_image = '/upload/images/icons/' . $ext . '.png';
            }

            return [
                'exist' => false,
                'file' => $_image,
            ];
        }

        /**
         * Вернет тип файла как заглушку
         */
        if (!in_array($ext, self::$allowExt)) {
            return [
                'exist' => file_exists($_SERVER['DOCUMENT_ROOT'] . $_image),
                'file' => '/upload/images/icons/' . $ext . '.png',
            ];
        }
        $imagesize = @getimagesize($_SERVER['DOCUMENT_ROOT'] . $_image);

        if (isset($imagesize[2])) {
            if (!in_array($imagesize[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF])) {
                return [
                    'exist' => true,
                    'file' => $_image,
                ];
            }
        } else {//ошибка чтения файла
            return [
                'exist' => true,
                'file' => $_image,
            ];
        }

        if ($imagesize[0] > $x || ($y && $imagesize[1] > $y)) {
            $dir = dirname($_image);
            $subDir = '.cache-' . implode('x', [$x, $y]);
            if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subDir)) {
                mkdir($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subDir);
                chmod($_SERVER['DOCUMENT_ROOT'] . $dir . '/' . $subDir, 0755);
            }
            $file = $dir . '/' . $subDir . '/' . $fileName;
            if (!file_exists($_SERVER['DOCUMENT_ROOT'] . $file)) {
                \Image::cache(function ($image) use ($_image, $file, $x, $y) {
                    $img = $image->make($_SERVER['DOCUMENT_ROOT'] . $_image)->resize($x, $y, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($_SERVER['DOCUMENT_ROOT'] . $file, 90);
                });

                return [
                    'exist' => true,
                    'file' => $file,
                ];
            } else {
                return [
                    'exist' => true,
                    'file' => $file,
                ];
            }
        }

        return [
            'exist' => true,
            'file' => $_image,
        ];
    }
}