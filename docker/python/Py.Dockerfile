FROM python:3.10
RUN apt-get update && apt-get upgrade -y
RUN pip install --upgrade pip
WORKDIR /app/pyFeeder
COPY /app/pyFeeder/requirements.txt .
RUN pip install -r requirements.txt