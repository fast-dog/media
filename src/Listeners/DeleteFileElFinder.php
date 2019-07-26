<?php

namespace FastDog\Media\Listeners;



use FastDog\Media\Models\GalleryItem;
use Illuminate\Http\Request;
use FastDog\Media\Events\DeleteFileElFinder as EventDeleteFileElFinder;

/**
 * Удаление файла через файловый менеджер elFinder
 *
 * @package FastDog\Media\Listeners
 * @version 0.1.5
 * @author Андрей Мартынов <d.g.dev482@gmail.com>
 */
class DeleteFileElFinder
{
    /**
     * @var Request $request
     */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * @param EventDeleteFileElFinder $event
     */
    public function handle(EventDeleteFileElFinder $event)
    {
        $response = $event->getData();
        $data = json_decode($response->getContent());
        if (isset($data->removed)) {
            foreach ($data->removed as $item) {
                $check = GalleryItem::where(GalleryItem::HASH, md5($item))->first();
                if ($check) {
                    GalleryItem::where('id', $check->id)->delete();
                }
            }
        }
    }
}
