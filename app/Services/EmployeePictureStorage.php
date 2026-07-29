<?php

namespace App\Services;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EmployeePictureStorage
{
    private const EXTENSION_BY_MIME = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
    ];

    /**
     * Store an image supplied either as a data URI or as plain base64.
     */
    public function putBase64($employeeId, string $encodedImage): bool
    {
        $imageData = $this->decode($encodedImage);
        if ($imageData === null) {
            return false;
        }

        $imageInfo = @getimagesizefromstring($imageData);
        $mime = is_array($imageInfo) ? ($imageInfo['mime'] ?? null) : null;
        if (!isset(self::EXTENSION_BY_MIME[$mime])) {
            return false;
        }

        $path = $this->pathFor($employeeId, self::EXTENSION_BY_MIME[$mime]);
        if (!$this->disk()->put($path, $imageData)) {
            throw new RuntimeException("Das Mitarbeiterfoto {$employeeId} konnte nicht gespeichert werden.");
        }

        $obsoletePaths = array_values(array_filter(
            $this->candidatePaths($employeeId),
            function ($candidatePath) use ($path) {
                return $candidatePath !== $path;
            }
        ));
        $this->disk()->delete($obsoletePaths);

        return true;
    }

    public function getBase64($employeeId): ?string
    {
        $path = $this->existingPath($employeeId);
        if ($path === null) {
            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mime = array_search($extension, self::EXTENSION_BY_MIME, true);
        if ($mime === false) {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($this->disk()->get($path));
    }

    /**
     * Return the absolute path used by FPDF.
     */
    public function absolutePath($employeeId): ?string
    {
        $path = $this->existingPath($employeeId);
        if ($path === null) {
            return null;
        }

        return $this->disk()->path($path);
    }

    private function decode(string $encodedImage): ?string
    {
        $encodedImage = trim($encodedImage);
        if (preg_match('/^data:image\/[^,]+;base64,(.*)$/si', $encodedImage, $matches)) {
            $encodedImage = $matches[1];
        }

        $encodedImage = preg_replace('/\s+/', '', $encodedImage);
        $imageData = base64_decode($encodedImage, true);

        return $imageData === false ? null : $imageData;
    }

    private function existingPath($employeeId): ?string
    {
        foreach ($this->candidatePaths($employeeId) as $path) {
            if ($this->disk()->exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function candidatePaths($employeeId): array
    {
        return array_map(function ($extension) use ($employeeId) {
            return $this->pathFor($employeeId, $extension);
        }, array_values(self::EXTENSION_BY_MIME));
    }

    private function pathFor($employeeId, string $extension): string
    {
        $employeeId = filter_var($employeeId, FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);
        if ($employeeId === false) {
            throw new RuntimeException('Ungültige Mitarbeiternummer für das Mitarbeiterfoto.');
        }

        return $employeeId.'.'.$extension;
    }

    private function disk(): Filesystem
    {
        return Storage::disk('employee-pictures');
    }
}
