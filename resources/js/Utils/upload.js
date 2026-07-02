/**
 * ==========================================================
 * Upload Utility
 * ==========================================================
 */

export function validateFile(

    file,

    maxSize = 2048

)
{

    return (

        file.size /

        1024 /

        1024

    ) <= maxSize

}

export function fileExtension(

    file

)
{

    return file.name

        .split('.')

        .pop()

        .toLowerCase()

}