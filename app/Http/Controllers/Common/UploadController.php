<?php

namespace App\Http\Controllers\Common;

use App\Exceptions\GraphQLException;
use App\Http\Controllers\Controller;
use App\Models\Custom;
use App\Models\Item;
use Illuminate\Http\Request;
use Storage;
use Str;

class UploadController extends Controller
{
    public function image(Request $request)
    {
        return $this->upload($request);
    }

    private function upload(Request $request)
    {
        $disk = \Storage::disk();
        $file = $request->file('file');
        if (!$file) {
            throw new GraphQLException('请上传文件');
        }
        $user = auth('user')->user();
        $projectId = $request->this_project_id;
        $path = uniqid($user->id . '/' . $projectId . '/' . now()->format('Y-m-d') . '/') . '.' . $file->getClientOriginalExtension();

        if ($result = $disk->put($path, file_get_contents($file->getRealPath()))) {
            $url = asset('upload/' . $path);
            $asset = Custom::where('project_id', $projectId)->first();
            $item = new Item();
            $item->project_id = $projectId;
            $item->custom_id = $asset->id;
            $item->content = [
                'name' => $file->getClientOriginalName(),
                'url' => $url,
                'is_system' => true,
                'type' => $file->getClientMimeType(),
                'file_size' => $file->getSize()
            ];
            $item->save();
            return [
                'code'       => 200,
                'data'       => [
                    'url'        => $url,
                    'name'       => $file->getClientOriginalName(),
                ]
            ];
        } else {
            \Log::error('上传失败', [$result]);
            throw new GraphQLException('上传失败');
        }
    }


}
