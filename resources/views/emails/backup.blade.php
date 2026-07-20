<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Respaldo de Base de Datos</title>
</head>
<body style="margin:0;padding:0;background-color:#f4f4f8;font-family:Arial,Helvetica,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f8;padding:20px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);padding:20px;text-align:center;">
                            <h1 style="color:#ffffff;margin:0;font-size:22px;font-weight:bold;">Consultorio Popular Tipo III</h1>
                            <h2 style="color:#f0f0f0;margin:5px 0 0;font-size:16px;">"El Chaparro de Guanta"</h2>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;line-height:1.6;color:#333333;">
                            <h2 style="color:#666;font-size:20px;margin:0 0 15px;">Respaldo de Base de Datos</h2>
                            <p style="font-size:15px;color:#555;margin:0 0 10px;">
                                Se ha generado el respaldo automático del sistema de gestión médica.
                            </p>
                            <table style="width:100%;border-collapse:collapse;margin:20px 0;">
                                <tr>
                                    <td style="padding:8px 12px;background:#f9f9f9;font-weight:bold;border:1px solid #e0e0e0;">Archivo</td>
                                    <td style="padding:8px 12px;background:#f9f9f9;border:1px solid #e0e0e0;">{{ $fileName }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 12px;border:1px solid #e0e0e0;font-weight:bold;">Tamaño</td>
                                    <td style="padding:8px 12px;border:1px solid #e0e0e0;">{{ $fileSize }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:8px 12px;background:#f9f9f9;border:1px solid #e0e0e0;font-weight:bold;">Fecha</td>
                                    <td style="padding:8px 12px;background:#f9f9f9;border:1px solid #e0e0e0;">{{ $date }}</td>
                                </tr>
                            </table>
                            <p style="font-size:14px;color:#888;margin:0;">
                                El archivo .sql se encuentra adjunto. Consérvalo en un lugar seguro.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 30px;border-top:1px solid #e0e0e0;text-align:center;">
                            <p style="font-size:13px;color:#888;margin:3px 0;">Consultorio Popular Tipo III "El Chaparro de Guanta"</p>
                            <p style="font-size:12px;color:#999;margin:3px 0;">Sistema de Gestión de Historias Clínicas</p>
                            <p style="font-size:11px;color:#aaa;margin:3px 0;">© {{ date('Y') }} - Todos los derechos reservados</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
